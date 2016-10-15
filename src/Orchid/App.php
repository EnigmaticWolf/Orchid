<?php

declare(strict_types = 1);

namespace AEngine\Orchid {

    use AEngine\Orchid\Entity\Exception\FileNotFoundException;
    use AEngine\Orchid\Entity\Exception\NoSuchMethodException;
    use AEngine\Orchid\Http\Environment;
    use AEngine\Orchid\Http\Headers;
    use AEngine\Orchid\Http\Request;
    use AEngine\Orchid\Http\Response;
    use Closure;
    use DirectoryIterator;
    use Exception;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use RuntimeException;

    class App
    {
        /**
         * Instance of class App
         *
         * @var App
         */
        protected static $instance;

        /**
         * @var array
         */
        protected $config = [];

        /**
         * @var array
         */
        protected $paths = [];

        /**
         * Storage closure of services
         *
         * @var array
         */
        protected $closures = [];

        /**
         * App constructor
         *
         * @param array $config
         */
        protected function __construct(array $config = [])
        {
            $self = $this;

            $this->config = array_replace_recursive([
                'debug'       => true,
                'app.name'    => 'public',
                'app.list'    => [],
                'module'      => [],
                'autoload'    => [],
                'module.list' => [],
                'secret'      => 'orchid secret',
                'args'        => [],
                'base_dir'    => '',
                'base_host'   => '',
                'base_port'   => 0,
            ], $config);

            // set base dir
            if (!$this->config['base_dir']) {
                if (!empty($_SERVER['DOCUMENT_ROOT'])) {
                    $this->config['base_dir'] = $_SERVER['DOCUMENT_ROOT'];
                } elseif (defined('ORCHID')) {
                    $this->config['base_dir'] = ORCHID;
                }
            }

            // set base host
            if (!$this->config['base_host'] && isset($_SERVER['HTTP_HOST'])) {
                $this->config['base_host'] = $_SERVER['HTTP_HOST'];
            }

            // set base port
            if (!$this->config['base_port'] && isset($_SERVER['SERVER_PORT'])) {
                $this->config['base_port'] = $_SERVER['SERVER_PORT'];
            }

            // cli mode
            if (PHP_SAPI == 'cli') {
                $this->config['args'] = array_slice($_SERVER['argv'], 1);
            }

            // register auto loader
            spl_autoload_register(function ($class) use ($self) {
                foreach ($self->config['autoload'] as $dir) {
                    $class_path = $dir . '/' . str_replace(['\\', '_'], '/', $class) . '.php';

                    if (file_exists($class_path)) {
                        require_once($class_path);

                        return;
                    }
                }
            });
        }

        /**
         * Return App instance
         *
         * @param array $config
         *
         * @return App
         */
        public static function getInstance(array $config = [])
        {
            if (!static::$instance) {
                static::$instance = new App($config);
            }

            return static::$instance;
        }

        /**
         * Return database
         *
         * @param array $configs
         *
         * @return Database
         */
        public function database(array $configs = [])
        {
            static $database;

            if (!$database) {
                if (!$configs) {
                    $configs = $this->get('database', []);
                }

                $database = new Database($this, $configs);
            }

            return $database;
        }

        /**
         * Return event
         *
         * @return Event
         */
        public function event()
        {
            static $event;

            if (!$event) {
                $event = new Event();
            }

            return $event;
        }

        /**
         * Return memory
         *
         * @param array $configs
         *
         * @return Memory
         */
        public function memory(array $configs = [])
        {
            static $memory;

            if (!$memory) {
                if (!$configs) {
                    $configs = $this->get('memory', []);
                }

                $memory = new Memory($this, $configs);
            }

            return $memory;
        }

        /**
         * Return request
         *
         * @return Request
         */
        public function request()
        {
            static $request;

            if (!$request) {
                $env = new Environment($_SERVER);
                $request = Request::createFromEnvironment($env);
            }

            return $request;
        }

        /**
         * Return response
         *
         * @return Response
         */
        public function response()
        {
            static $response;

            if (!$response) {
                $headers = new Headers(['Content-Type' => 'text/html; charset=UTF-8']);
                $response = (new Response(200, $headers))->withProtocolVersion('1.1');
            }

            return $response;
        }

        /**
         * Return router
         *
         * @return Router
         */
        public function router()
        {
            static $router;

            if (!$router) {
                $router = new Router();
            }

            return $router;
        }

        /**
         * Return debug flag
         *
         * @return bool
         */
        public function isDebug()
        {
            return $this->get('debug', true);
        }

        /**
         * Return value from internal config
         *
         * @param string $key
         * @param mixed  $default
         *
         * @return mixed
         */
        public function get($key, $default = null)
        {
            return $this->config[$key] ?? $default;
        }

        /**
         * Add value for name (not necessary) in array with key
         *
         * <code>
         * $app->add('array', 'bar'); // add index with value 'bar'
         * $app->add('array', 'foo', 'bar'); // add key 'foo' with value 'bar'
         * </code>
         *
         * @param string $key
         * @param array  $element
         *
         * @return App
         */
        public function add($key, ...$element)
        {
            switch (count($element)) {
                case 1:
                    $this->config[$key][] = $element[0];
                    break;
                case 2:
                    $this->config[$key][$element[0]] = $element[1];
                    break;
            }

            return $this;
        }

        /**
         * Set value for key
         *
         * @param string $key
         * @param mixed  $value
         *
         * @return App
         */
        public function set($key, $value)
        {
            $this->config[$key] = $value;

            return $this;
        }

        /**
         * Return current app name
         *
         * @return string
         */
        public function getApp()
        {
            return $this->get('app.name', 'public');
        }

        /**
         * Set app name
         *
         * @param $name
         *
         * @return bool
         * @throws RuntimeException
         */
        public function setApp($name)
        {
            if (in_array($name, $this->get('app.list', []))) {
                $this->config['app.name'] = $name;

                return true;
            }

            throw new RuntimeException('Application "' . $name . '" not found in "app.list"');
        }

        /**
         * Load modules from specified folders
         *
         * @param array $folders
         *
         * @return App
         * @throws FileNotFoundException
         * @throws NoSuchMethodException
         * @throws RuntimeException
         */
        public function loadModule(array $folders)
        {
            foreach ($folders as $folder) {
                // add folder to autoload
                $this->config['autoload'][] = $folder;

                foreach (new DirectoryIterator($folder) as $element) {
                    if (!$element->isDot() && (
                            $element->isDir() ||
                            $element->isFile() && $element->getExtension() == 'php'
                        )
                    ) {
                        $dir = $element->getRealPath();
                        $name = $class = $element->getBasename('.php');

                        if (!is_file($dir)) {
                            $this->path($class, $dir);
                            $dir = $dir . DIRECTORY_SEPARATOR . 'Module' . $class . '.php';

                            // class name with namespace
                            $class = $element->getFilename() . '\\Module' . $class;
                        }

                        if (file_exists($dir)) {
                            require_once($dir);
                        } else {
                            throw new FileNotFoundException('Could not find specified file');
                        }

                        // check exists and parent class
                        if (class_exists($class) && is_subclass_of($class, 'Orchid\\Entity\\Module')) {
                            // call initialize method
                            call_user_func([$class, 'initialize'], $this);
                        } else {
                            throw new RuntimeException(
                                'Class "' . $class . '" not found or is not a subclass of "Orchid\\Entity\\Module"'
                            );
                        }

                        $this->config['module.list'][] = $name;
                    }
                }
            }

            return $this;
        }

        /**
         * Path helper method
         *
         * <code>
         * // set path shortcut
         * $app->path('cache', ORCHID . '/storage/cache');
         *
         * // get path for file
         * $app->path('cache:filename.cache');
         * </code>
         *
         * @param $shortcut
         * @param $path
         *
         * @return App|bool|string
         */
        public function path($shortcut, $path = '')
        {
            if ($shortcut && $path) {
                $path = str_replace(DIRECTORY_SEPARATOR, '/', $path);

                if (!isset($this->paths[$shortcut])) {
                    $this->paths[$shortcut] = [];
                }

                array_unshift($this->paths[$shortcut], is_file($path) ? $path : $path . '/');

                return $this;
            } else {
                if (static::isAbsolutePath($shortcut) && file_exists($shortcut)) {
                    return $shortcut;
                }

                if (($parts = explode(':', $shortcut, 2)) && count($parts) == 2) {
                    if (isset($this->paths[$parts[0]])) {
                        foreach ($this->paths[$parts[0]] as &$shortcut) {
                            if (file_exists($shortcut . $parts[1])) {
                                return $shortcut . $parts[1];
                            }
                        }
                    }
                }
            }

            return false;
        }

        /**
         * Checks is absolute path
         *
         * @param $path
         *
         * @return bool
         */
        public static function isAbsolutePath($path)
        {
            return $path && (
                '/' == $path[0] ||
                '\\' == $path[0] ||
                (3 < mb_strlen($path) && ctype_alpha($path[0]) && $path[1] == ':' &&
                    (
                        '\\' == $path[2] ||
                        '/' == $path[2]
                    )
                )
            );
        }

        /**
         * Return array of loaded modules
         *
         * @return array
         */
        public function getModules()
        {
            return $this->get('module.list', []);
        }

        /**
         * Return secret word
         *
         * @return string
         */
        public function getSecret()
        {
            return $this->get('secret', 'secret');
        }

        /**
         * Return CLI args
         *
         * @return array
         */
        public function getArgs()
        {
            return $this->get('args', []);
        }

        /**
         * Return base dir
         *
         * @return string
         */
        public function getBaseDir()
        {
            return $this->get('base_dir');
        }

        /**
         * Return base host name
         *
         * @return string
         */
        public function getBaseHost()
        {
            return $this->get('base_host');
        }

        /**
         * Return base port num
         *
         * @return int
         */
        public function getBasePort()
        {
            return (int)$this->get('base_port');
        }

        /**
         * Return path list by shortcut
         *
         * @param $shortcut
         *
         * @return array
         */
        public function pathList($shortcut)
        {
            return $this->paths[$shortcut] ?? [];
        }

        /**
         * Convert shortcut to uri
         *
         * @param $path
         *
         * @return bool|string
         */
        public function pathToUrl($path)
        {
            if (($file = $this->path($path)) != false) {
                return '/' . ltrim(str_replace($this->get('base_dir'), '', $file), '/');
            }

            return false;
        }

        /**
         * Run Application
         *
         * This method traverses the application middleware stack and then sends the
         * resultant Response object to the HTTP client.
         *
         * @param bool $silent
         *
         * @return ResponseInterface
         */
        public function run($silent = false)
        {
            $response = $this->process($this->request(), $this->response());

            if (!$silent) {
                $this->respond($response);
            }

            return $response;
        }

        /**
         * Process a request
         *
         * This method traverses the application middleware stack and then returns the
         * resultant Response object.
         *
         * @param ServerRequestInterface $request
         * @param ResponseInterface      $response
         *
         * @return ResponseInterface
         *
         * @throws Exception
         * @throws NoSuchMethodException
         */
        public function process(ServerRequestInterface $request, ResponseInterface $response)
        {
            $router = new Router();

            return $response;
        }

        /**
         * Send the response the client
         *
         * @param ResponseInterface $response
         */
        public function respond(ResponseInterface $response)
        {
            // Send response
            if (!headers_sent()) {
                // Status
                header(sprintf(
                    'HTTP/%s %s %s',
                    $response->getProtocolVersion(),
                    $response->getStatusCode(),
                    $response->getReasonPhrase()
                ));
                // Headers
                foreach ($response->getHeaders() as $name => $values) {
                    foreach ($values as $value) {
                        header(sprintf('%s: %s', $name, $value), false);
                    }
                }
            }

            // Body
            if (!$this->isEmptyResponse($response)) {
                $body = $response->getBody();
                if ($body->isSeekable()) {
                    $body->rewind();
                }
                $chunkSize = 4096;
                $contentLength = $response->getHeaderLine('Content-Length');
                if (!$contentLength) {
                    $contentLength = $body->getSize();
                }
                if (isset($contentLength)) {
                    $amountToRead = $contentLength;
                    while ($amountToRead > 0 && !$body->eof()) {
                        $data = $body->read(min($chunkSize, $amountToRead));
                        echo $data;

                        $amountToRead -= strlen($data);

                        if (connection_status() != CONNECTION_NORMAL) {
                            break;
                        }
                    }
                } else {
                    while (!$body->eof()) {
                        echo $body->read($chunkSize);
                        if (connection_status() != CONNECTION_NORMAL) {
                            break;
                        }
                    }
                }
            }
        }

        /**
         * Finalize response
         *
         * @param ResponseInterface $response
         *
         * @return ResponseInterface
         */
        protected function finalize(ResponseInterface $response)
        {
            // stop PHP sending a Content-Type automatically
            ini_set('default_mimetype', '');

            if ($this->isEmptyResponse($response)) {
                return $response->withoutHeader('Content-Type')->withoutHeader('Content-Length');
            }

            // Add Content-Length header
            if (true) { // TODO: add in settings
                if (ob_get_length() > 0) {
                    throw new RuntimeException(
                        "Unexpected data in output buffer. "
                        . "Maybe you have characters before an opening <?php tag?"
                    );
                }
                $size = $response->getBody()->getSize();
                if ($size !== null && !$response->hasHeader('Content-Length')) {
                    $response = $response->withHeader('Content-Length', (string)$size);
                }
            }

            return $response;
        }

        /**
         * Helper method, which returns true if the provided response must not output a body and false
         * if the response could have a body.
         *
         * @see https://tools.ietf.org/html/rfc7231
         *
         * @param ResponseInterface $response
         *
         * @return bool
         */
        protected function isEmptyResponse(ResponseInterface $response)
        {
            if (method_exists($response, 'isEmpty')) {
                return $response->isEmpty();
            }

            return in_array($response->getStatusCode(), [204, 205, 304]);
        }

        /**
         * Add closure
         *
         * @param string  $name
         * @param Closure $callable
         *
         * @return bool
         * @throws RuntimeException
         */
        public function addClosure($name, $callable)
        {
            if (is_string($name) && !isset($this->closures[$name])) {
                $this->closures[$name] = function ($param = null) use ($callable) {
                    static $object;

                    if ($object === null) {
                        $object = $callable($param);
                    }

                    return $object;
                };

                return true;
            }

            throw new RuntimeException('Failed to add closure "' . $name . '"');
        }

        /**
         * Return the result of the work closure
         *
         * @param string $name
         * @param array  ...$param
         *
         * @return mixed
         * @throws RuntimeException
         */
        public function getClosure($name, ...$param)
        {
            if (is_string($name) && array_key_exists($name, $this->closures) && is_callable($this->closures[$name])) {
                return call_user_func_array($this->closures[$name], $param);
            }

            throw new RuntimeException('Unable to complete closure "' . $name . '"');
        }

        protected function __clone()
        {
        }
    }
}

namespace {

    // function for debugging
    function pre(...$args)
    {
        echo '<pre>';
        foreach ($args as $obj) {
            var_dump($obj);
        }
        echo '</pre>';
    }
}
