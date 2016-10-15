<?php

namespace AEngine\Orchid;

use AEngine\Orchid\Entity\Exception\NoSuchMethodException;
use AEngine\Orchid\Http\Request;
use Closure;
use Psr\Http\Middleware\MiddlewareInterface;
use RuntimeException;

class Router
{
    /**
     * @var App
     */
    protected $app;

    /**
     * Array of route rules
     *
     * @var array
     */
    protected $routes = [];

    /**
     * Router constructor
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Bind GET request to route
     *
     * Example:
     * <code>
     * $router->get('/', function() {
     *  return 'this is GET Request';
     * }, $priority = 10);
     * // or
     * $router->get('/', '\\Namespace\\ClassName', $priority = 10);
     * </code>
     *
     * @see Router::bind
     *
     * @param string         $pattern
     * @param Closure|string $callable
     * @param int            $priority
     *
     * @return Router
     */
    public function get($pattern, $callable, $priority = 0)
    {
        return $this->bind($pattern, $callable, Request::METHOD_GET, $priority);
    }

    /**
     * Bind POST request to route
     *
     * Example:
     * <code>
     * $router->post('/', function() {
     *  return 'this is POST Request';
     * }, $priority = 10);
     * // or
     * $router->post('/', '\\Namespace\\ClassName', $priority = 10);
     * </code>
     *
     * @see Router::bind
     *
     * @param string         $pattern
     * @param Closure|string $callable
     * @param int            $priority
     *
     * @return Router
     */
    public function post($pattern, $callable, $priority = 0)
    {
        return $this->bind($pattern, $callable, Request::METHOD_POST, $priority);
    }

    /**
     * Bind request to route
     *
     * Example:
     * <code>
     * $router->bind('/', function() {
     *  return 'this is GET or POST Request';
     * }, Request::METHOD_GET, $priority = 10);
     * // or
     * $router->bind('/', '\\Namespace\\ClassName', $priority = 10);
     * </code>
     *
     * Params example:
     * <code>
     * $router->get('/news/:date/:id', function($params) {
     *  return $params['date'].'-'.$params['id'];
     * }, Request::METHOD_GET, $priority = 10);
     * </code>
     *
     * Mask example:
     * <code>
     * $router->post('/file/*', function($params) {
     *  return $params[':arg'];
     * }, Request::METHOD_GET, $priority = 10);
     * </code>
     *
     * Regex example:
     * <code>
     * $router->bind('#/page/(about|contact)#', function($params) {
     *  return implode('\n', $params[':capture']);
     * }, Request::METHOD_GET, $priority = 10);
     * </code>
     *
     * @param string         $pattern
     * @param Closure|string $callable
     * @param string         $method
     * @param int            $priority
     *
     * @return Router
     */
    public function bind($pattern, $callable, $method = null, $priority = 0)
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'pattern'    => $pattern,
            'callable'   => $callable,
            'priority'   => $priority,
            'middleware' => [],
        ];

        return $this;
    }

    /**
     * Attach middleware to last added route
     *
     * @param  array|string|null $middleware
     *
     * @return array|Router
     */
    public function middleware(...$middleware)
    {
        if ($middleware) {
            foreach ($middleware as $key) {
                if (is_a($key, MiddlewareInterface::class, true)) {
                    $this->routes[count($this->routes) - 1]['middleware'][] = $key;
                }
            }
        }

        return $this;
    }

    /**
     * Dispatch route
     *
     * Runs the appropriate action
     * It will execute the before() method before the action
     * and after() method after the action finishes
     *
     * @return string|null
     * @throws RuntimeException
     * @throws NoSuchMethodException
     */
    public function dispatch()
    {
        $request = $this->app->request();
        $method = $request->getMethod();
        $pathname = $request->getPathname();
        $uri = $request->getUri();

        $found = null;
        $params = [];

        if ($this->routes) {
            usort($this->routes, [$this, 'compare']);

            foreach ($this->routes as $route) {
                if ($route['method'] == $method || !$route['method']) {
                    if ($route['pattern'] === $pathname) {
                        $found = $route;
                        break;
                    }

                    /* #\.html$#  */
                    if (substr($route['pattern'], 0, 1) == '#' && substr($route['pattern'], -1) == '#') {
                        if (preg_match($route['pattern'], $pathname, $match)) {
                            $params[':capture'] = array_slice($match, 1);
                            $found = $route;
                            break;
                        }
                    }

                    /* /example/* */
                    if (strpos($route['pattern'], '*') !== false) {
                        $pattern = '#^' . str_replace('\\*', '(.*)', preg_quote($route['pattern'], '#')) . '#';
                        if (preg_match($pattern, $pathname, $match)) {
                            $params[':arg'] = array_slice($match, 1);
                            $found = $route;
                            break;
                        }
                    }

                    /* /example/:id */
                    if (strpos($route['pattern'], ':') !== false) {
                        $parts = explode('/', $route['pattern']);
                        array_shift($parts);

                        if (count($uri) == count($parts)) {
                            $matched = true;
                            foreach ($parts as $index => $part) {
                                if (':' === substr($part, 0, 1)) {
                                    $params[substr($part, 1)] = $uri[$index];
                                    continue;
                                }
                                if ($uri[$index] != $parts[$index]) {
                                    $matched = false;
                                    break;
                                }
                            }
                            if ($matched) {
                                $found = $route;
                                break;
                            }
                        }
                    }
                }
            }
        } else {
            throw new RuntimeException('Route list is empty');
        }

        if ($found) {
            $continue = !count($found['middleware']);

            if ($found['middleware']) {
                // execute all middleware
                foreach ($found['middleware'] as $key) {
                    $continue = call_user_func_array([$key, 'handle'], [$request, $this->app->response()]);
                }
            }

            // continue if middlewares allow
            if ($continue === true) {
                $action = $request->getUri(-1, 'index');

                if (is_object($found['callable'])) {
                    return call_user_func($found['callable'], $action, $params);
                }
                if (is_string($found['callable'])) {
                    $controller = new $found['callable']($this->app);
                    $result = null;

                    if (method_exists($controller, 'before')) {
                        call_user_func([$controller, 'before'], $action, $params);
                    }

                    if ($controller->execute) {
                        if (method_exists($controller, $action)) {
                            $result = call_user_func([$controller, $action], $params);
                        } else {
                            throw new NoSuchMethodException(
                                'Method "' . $action . '" doesn\'t exist in "' . get_class($controller) . '"'
                            );
                        }
                    }

                    if ($controller->execute) {
                        if (method_exists($controller, 'after')) {
                            call_user_func([$controller, 'after'], $action, $params);
                        }
                    }

                    return $result;
                }
            }
        } else {
            throw new RuntimeException('Failed to find and execute the function');
        }

        return null;
    }

    /**
     * @param $a
     * @param $b
     *
     * @return mixed
     */
    protected function compare($a, $b)
    {
        return $b['priority'] - $a['priority'];
    }
}
