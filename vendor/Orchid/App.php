<?php

namespace Orchid;

use Closure;
use DirectoryIterator;
use Exception;
use RuntimeException;
use Orchid\Entity\Exception\FileNotFoundException;
use Orchid\Entity\Exception\NoSuchMethodException;

class App {
	/**
	 * @var array
	 */
	protected $config = [];

	/**
	 * @var array
	 */
	protected $paths = [];

	/**
	 * Instance of class App
	 *
	 * @var App
	 */
	protected static $instance;

	/**
	 * App constructor
	 *
	 * @param array $config
	 */
	public function __construct(array $config = []) {
		if (static::$instance) {
			return static::$instance;
		}
		static::$instance = $self = $this;

		$this->config = array_merge_recursive([
			"debug"       => true,
			"app.name"    => "public",
			"app.list"    => [],
			"module"      => [],
			"autoload"    => [],
			"module.list" => [],
			"secret"      => "orchid secret",
			"args"        => [],
			"base_dir"    => "",
			"base_host"   => "",
			"base_port"   => 0,
		], $config);

		// set base dir
		if (!$this->config["base_dir"]) {
			if (!empty($_SERVER["DOCUMENT_ROOT"])) {
				$this->config["base_dir"] = $_SERVER["DOCUMENT_ROOT"];
			} elseif (defined("ORCHID")) {
				$this->config["base_dir"] = ORCHID;
			}
		}

		// set base host
		if (!$this->config["base_host"] && isset($_SERVER["HTTP_HOST"])) {
			$this->config["base_host"] = $_SERVER["HTTP_HOST"];
		}

		// set base port
		if (!$this->config["base_port"] && isset($_SERVER["SERVER_PORT"])) {
			$this->config["base_port"] = $_SERVER["SERVER_PORT"];
		}

		// register autoloader
		spl_autoload_register(function ($class) use ($self) {
			foreach ($self->config["autoload"] as $dir) {
				$class_path = $dir . "/" . str_replace(["\\", "_"], "/", $class) . ".php";

				if (file_exists($class_path)) {
					require_once($class_path);

					return;
				}
			}
		});

		// not cli mode
		if (PHP_SAPI != "cli") {
			set_exception_handler(function (Exception $ex) {
				@ob_end_clean();

				if ($this->isDebug()) {
					$message = "Exception: " . $ex->getMessage() . " (code " . $ex->getCode() . ")\nFile: " . $ex->getFile() . " (at " . $ex->getLine() . " line)\nTrace:\n" . $ex->getTraceAsString();
				} else {
					$message = "Internal Error";
				}

				$this->response()
				     ->setStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
				     ->setHeader("Content-Type", "text/plain")
				     ->setContent($message);
			});

			// обработка заверщения работы
			register_shutdown_function(function () {
				if (($error = error_get_last()) && error_reporting() & $error["type"]) {
					@ob_end_clean();

					if ($this->isDebug()) {
						$message = "ERROR: " . $error["message"] . " (code " . $error["type"] . ")\nFile: " . $error["file"] . " (at " . $error["line"] . " line)";
					} else {
						$message = "Internal Error";
					}

					$this->response()
					     ->setStatus(Response::HTTP_INTERNAL_SERVER_ERROR)
					     ->setHeader("Content-Type", "text/plain")
					     ->setContent($message);
				} else {
					if ($this->response()->isOk() && !$this->response()->getContent()) {
						$this->response()
						     ->setStatus(Response::HTTP_NOT_FOUND)
						     ->setContent("Path not found");
					}
				}

				$this->event()->trigger("shutdown");
				$this->response()->send();
			});
		} else {
			$this->config["args"] = array_slice($_SERVER["argv"], 1);
		}

		return $this;
	}

	/**
	 * Return App instance
	 *
	 * @return App
	 */
	public static function getInstance() {
		if (static::$instance) {
			return static::$instance;
		}

		throw new RuntimeException("Instance of class App was not created");
	}

	/**
	 * Return value from internal config
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if (!empty($this->config[$key])) {
			return $this->config[$key];
		}

		return $default;
	}

	/**
	 * Return request
	 *
	 * @return Request
	 */
	public function request() {
		static $request;

		if (!$request) {
			$request = new Request($_POST, $_FILES, $_COOKIE);
		}

		return $request;
	}

	/**
	 * Return router
	 *
	 * @return Router
	 */
	public function router() {
		static $router;

		if (!$router) {
			$router = new Router($this);
		}

		return $router;
	}

	/**
	 * Return response
	 *
	 * @return Response
	 */
	public function response() {
		static $response;

		if (!$response) {
			$response = new Response();
		}

		return $response;
	}

	/**
	 * Return database
	 *
	 * @param array $configs
	 *
	 * @return Database
	 */
	public function database(array $configs = []) {
		static $database;

		if (!$database) {
			if (!$configs) {
				$configs = $this->get("database", []);
			}

			$database = new Database($this, $configs);
		}

		return $database;
	}

	/**
	 * Return memory
	 *
	 * @param array $configs
	 *
	 * @return Memory
	 */
	public function memory(array $configs = []) {
		static $memory;

		if (!$memory) {
			if (!$configs) {
				$configs = $this->get("memory", []);
			}

			$memory = new Memory($this, $configs);
		}

		return $memory;
	}

	/**
	 * Return event
	 *
	 * @return Event
	 */
	public function event() {
		static $event;

		if (!$event) {
			$event = new Event();
		}

		return $event;
	}

	/**
	 * Return debug flag
	 *
	 * @return bool
	 */
	public function isDebug() {
		return $this->get("debug", true);
	}

	/**
	 * Return current app name
	 *
	 * @return string
	 */
	public function getApp() {
		return $this->get("app.name", "public");
	}

	/**
	 * Set app name
	 *
	 * @param $name
	 *
	 * @return bool
	 */
	public function setApp($name) {
		if (in_array($name, $this->get("app.list", []))) {
			$this->config["app.name"] = $name;

			return true;
		}

		return false;
	}

	/**
	 * Load modules from specified folders
	 *
	 * @param array $folders
	 *
	 * @return $this
	 * @throws FileNotFoundException
	 * @throws NoSuchMethodException
	 * @throws RuntimeException
	 */
	public function loadModule(array $folders) {
		foreach ($folders as $folder) {
			foreach (new DirectoryIterator($folder) as $element) {
				if (!$element->isDot() && ($element->isDir() || $element->isFile() && $element->getExtension() == "php")) {
					$dir = $element->getRealPath();
					$name = $class = $element->getBasename(".php");

					if (!is_file($dir)) {
						$this->path($class, $dir);
						$dir = $dir . DIRECTORY_SEPARATOR . "Module" . $class . ".php";

						// class name with namespace
						$class = $element->getFilename() . "\\Module" . $class;
					}

					if (file_exists($dir)) {
						require_once($dir);
					} else {
						throw new FileNotFoundException("Could not find specified file");
					}

					// check exists and parent class
					if (class_exists($class) && is_subclass_of($class, 'Orchid\\Entity\\Module')) {

						// check have method initialize
						if (method_exists($class, "initialize")) {
							call_user_func([$class, "initialize"], $this);
						} else {
							throw new NoSuchMethodException("Initialize method is not found in class '" . $class . "'");
						}
					} else {
						throw new RuntimeException("Class '" . $class . "' not found or is not a subclass of 'Orchid\\Entity\\Module'");
					}

					$this->config["module.list"][] = $name;
				}
			}

			// add folder to autoload
			$this->config["autoload"][] = $folder;
		}

		return $this;
	}

	/**
	 * Return array of loaded modules
	 *
	 * @return array
	 */
	public function getModules() {
		return $this->get("module.list", []);
	}

	/**
	 * Return secret word
	 *
	 * @return string
	 */
	public function getSecret() {
		return $this->get("secret", "secret");
	}

	/**
	 * Return CLI args
	 *
	 * @return array
	 */
	public function getArgs() {
		return $this->get("args", []);
	}

	/**
	 * Return base dir
	 *
	 * @return string
	 */
	public function getBaseDir() {
		return $this->get("base_dir");
	}

	/**
	 * Return base host name
	 *
	 * @return string
	 */
	public function getBaseHost() {
		return $this->get("base_host");
	}

	/**
	 * Return base port num
	 *
	 * @return int
	 */
	public function getBasePort() {
		return (int)$this->get("base_port");
	}

	/**
	 * Path helper method
	 *
	 * <code>
	 * // set path shortcut
	 * $app->path("cache", ORCHID . "/storage/cache");
	 *
	 * // get path for file
	 * $app->path("cache:someone.cache");
	 * </code>
	 *
	 * @param $shortcut
	 * @param $path
	 *
	 * @return $this|string
	 */
	public function path($shortcut, $path = "") {
		if ($shortcut && $path) {
			$path = str_replace(DIRECTORY_SEPARATOR, "/", $path);

			if (!isset($this->paths[$shortcut])) {
				$this->paths[$shortcut] = [];
			}

			array_unshift($this->paths[$shortcut], is_file($path) ? $path : $path . "/");

			return $this;
		} else {
			if (static::isAbsolutePath($shortcut) && file_exists($shortcut)) {
				return $shortcut;
			}

			if (($parts = explode(":", $shortcut, 2)) && count($parts) == 2) {
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
	 * Convert shortcut to uri
	 *
	 * @param $path
	 *
	 * @return bool|string
	 */
	public function pathToUrl($path) {
		if (($file = $this->path($path)) != false) {
			return "/" . ltrim(str_replace($this->get("base_dir"), "", $file), "/");
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
	public static function isAbsolutePath($path) {
		return $path && ("/" == $path[0] || "\\" == $path[0] || (3 < mb_strlen($path) && ctype_alpha($path[0]) && $path[1] == ":" && ("\\" == $path[2] || "/" == $path[2])));
	}

	/**
	 * Run Application
	 *
	 * @return $this
	 * @throws NoSuchMethodException
	 */
	public function run() {
		@ob_start("ob_gzhandler");
		@ob_implicit_flush(false);

		// trigger before route event
		$this->event()->trigger("before");

		// route and set response content
		$this->response()->setContent($this->router()->dispatch());

		// trigger after route event
		$this->event()->trigger("after");

		return $this;
	}

	/**
	 * Storage closure of services
	 *
	 * @var array
	 */
	protected $closures = [];

	/**
	 * Add closure
	 *
	 * @param string  $name
	 * @param Closure $callable
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public function addClosure($name, $callable) {
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

		throw new RuntimeException("Failed to add closure '" . $name . "'");
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
	public function getClosure($name, ...$param) {
		if (is_string($name) && array_key_exists($name, $this->closures) && is_callable($this->closures[$name])) {
			return call_user_func_array($this->closures[$name], $param);
		}

		throw new RuntimeException("Unable to complete closure '" . $name . "'");
	}

	private function __clone() {
	}
}
