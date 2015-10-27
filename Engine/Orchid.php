<?php

/*
 * Copyright (c) 2011-2016 AEngine
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Engine;

use ArrayAccess;
use Closure;
use DirectoryIterator;
use Engine\Entity\Module;
use Engine\Entity\Extension;
use InvalidArgumentException;
use SplPriorityQueue;

class Orchid implements ArrayAccess {
	/** @var Orchid $instance */
	public static $instance = null;

	protected $registry = [];
	protected $exit     = false;

	/** @var Response $response */
	public $response = null;

	public function __construct(array $param = []) {
		$this->registry = array_merge([
			"debug"		=> true,

			"app"		=> "Orchid-App",
			"secret"    => "Orchid-Secret",
			"session"   => "Orchid-Session",

			"extension" => [],
			"module"    => [],
			"path"      => [],
			"task"      => [],
			"route"     => [],

			"uri"       => [],
			"param"     => [],
			"data"      => [],
			"args"      => [],

			"base_dir"  => !empty($_SERVER["DOCUMENT_ROOT"])	? $_SERVER["DOCUMENT_ROOT"] : dirname($_SERVER["PHP_SELF"]),
			"base_host" => !empty($_SERVER["SERVER_NAME"])		? $_SERVER["SERVER_NAME"]	: "",
			"base_port" => (int)(isset($_SERVER["SERVER_PORT"])	? $_SERVER["SERVER_PORT"]	: 80),
		], $param);

		static::$instance = &$this;

		// SLI аргументы
		if (PHP_SAPI == "cli"){
			$this->registry["args"] = array_slice($_SERVER['argv'], 1);
		}

		// Заполняем URI
		foreach (explode("/", parse_url(urldecode(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : ""), PHP_URL_PATH)) as $part) {
			if ($part) {
				$this->registry["uri"][] = $part;
			}
		}

		// Переписываем GET
		$_GET = [];
		foreach (explode("&", parse_url(urldecode(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : ""), PHP_URL_QUERY)) as $part) {
			if ($part) {
				$data                              = explode("=", $part);
				$this->registry["param"][$data[0]] = $_GET[$data[0]] = $data[1];
			}
		}

		// Проверяем php://input и объединяем с $_POST
		if (
			(
				isset($_SERVER["CONTENT_TYPE"]) &&
				stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false
			) ||
			(
				isset($_SERVER["HTTP_CONTENT_TYPE"]) &&
				stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") !== false
			)
		) {
			if ($json = json_decode(@file_get_contents("php://input"), true)) {
				$_POST = array_merge($_POST, $json);
			}
		}

		$this->registry["data"] = $_POST;
		$_REQUEST               = array_merge($_GET, $_POST, $_COOKIE);
	}

	/**
	 * @return Orchid
	 */
	public static function &getInstance() {
		return static::$instance;
	}

	/**
	 * @param $type
	 * @return bool|int
	 */
	public function req_is($type) {
		switch (strtolower($type)) {
			case "ajax": {
				return (
					(
						isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
						$_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest"
					) || (
						isset($_SERVER["CONTENT_TYPE"]) &&
						stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false
					) ||
					(
						isset($_SERVER["HTTP_CONTENT_TYPE"]) &&
						stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") !== false
					)
				);
			}

			case "mobile": {
				$mobileDevices = [
					"midp", "240x320", "blackberry", "netfront", "nokia", "panasonic", "portalmmm",
					"sharp", "sie-", "sonyericsson", "symbian", "windows ce", "benq", "mda", "mot-",
					"opera mini", "philips", "pocket pc", "sagem", "samsung", "sda", "sgh-", "vodafone",
					"xda", "iphone", "ipod", "android",
				];

				return preg_match("/(" . implode("|", $mobileDevices) . ")/i", strtolower($_SERVER["HTTP_USER_AGENT"]));
			}

			case "head": {
				return (strtolower($_SERVER["REQUEST_METHOD"]) == "head");
			}

			case "put": {
				return (strtolower($_SERVER["REQUEST_METHOD"]) == "put");
			}

			case "post": {
				return (strtolower($_SERVER["REQUEST_METHOD"]) == "post");
			}

			case "get": {
				return (strtolower($_SERVER["REQUEST_METHOD"]) == "get");
			}

			case "delete": {
				return (strtolower($_SERVER["REQUEST_METHOD"]) == "delete");
			}

			case "options": {
				return (strtolower($_SERVER["REQUEST_METHOD"]) == "options");
			}

			case "ssl": {
				return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
			}
		}

		return false;
	}

	/**
	 * Возвращает IP адрес клиента
	 * @return string
	 */
	public function getClientIp() {
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		} elseif (isset($_SERVER["REMOTE_ADDR"])) {
			return $_SERVER["REMOTE_ADDR"];
		}

		return false;
	}

	/**
	 * Возвращает язык клиента
	 * @param string $default по умолчанию русский
	 * @return string
	 */
	public function getClientLang($default = "ru") {
		// todo починить
		if (!isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
			return $default;
		}

		return strtolower(substr($_SERVER["HTTP_ACCEPT_LANGUAGE"], 0, 2));
	}

	/**
	 * Возвращает адрес сайта
	 * @param bool $withPath
	 * @return string
	 */
	public function getSiteUrl($withPath = false) {
		$url = ($this->req_is("ssl") ? "https" : "http") . "://";
		$url .= $this->registry["base_host"];
		if ($this->registry["base_port"] != "80") {
			$url .= ":" . $this->registry["base_port"];
		}
		if ($withPath) {
			$url .= $this->getSitePath();;
		}

		return rtrim($url, "/");
	}

	/**
	 * Возвращает путь
	 * @return string
	 */
	public function getSitePath() {
		return implode("/", $this["uri"]);
	}

	/**
	 * Добавляет задачу
	 * @param  string	$name     имя задачи
	 * @param  Closure	$callback функция
	 * @param  int 		$priority приоритет задачи
	 * @return Orchid
	 */
	public function task($name, $callback, $priority = 0) {
		$name = strtolower($name);
		if (!isset($this->registry["task"][$name])) {
			$this->registry["task"][$name] = [];
		}

		if (is_object($callback) && $callback instanceof Closure) {
			$callback = $callback->bindTo($this, $this);
		}

		$this->registry["task"][$name][] = ["callback" => $callback, "priority" => $priority];

		return $this;
	}

	/**
	 * Запускает выполнение задачи с возможностью передачи параметров
	 * @param  string $name   имя задачи
	 * @param  array  $params передаваемые параметры
	 * @return Orchid
	 */
	public function trigger($name, $params = []) {
		if (!isset($this->registry["task"][$name])) {
			return $this;
		}
		if (!count($this->registry["task"][$name])) {
			return $this;
		}

		$queue = new SplPriorityQueue();
		foreach ($this->registry["task"][$name] as $index => $action) {
			$queue->insert($index, $action["priority"]);
		}

		$queue->top();
		while ($queue->valid()) {
			$index = $queue->current();
			if (is_callable($this->registry["task"][$name][$index]["callback"])) {
				if (call_user_func_array($this->registry["task"][$name][$index]["callback"], $params) === false) {
					break; //остановить
				}
			}
			$queue->next();
		}

		return $this;
	}

	/**
	 * Запуск приложения
	 * @return Orchid
	 */
	public function run() {
		if (PHP_SAPI == "cli" && isset($this["args"][0])) {
			$this->bootDaemon(); // запускаем демона
		} else {
			$self = $this;
			register_shutdown_function(function () use ($self) {
				// Если приложение было завершено
				if ($self->isTerminated()) {
					return;
				}

				$error = error_get_last();
				if ($error && in_array($error["type"], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_USER_ERROR])) {
					ob_end_clean();
					$self->response->nocache = true;
					$self->response->status  = "500";
					$self->response->body    = $self["debug"] ? $error : "Internal Error.";
				} elseif (!$self->response->body) {
					$self->response->nocache = true;
					$self->response->status  = "404";
					$self->response->body    = "Path not found.";
				}

				$self->trigger("after");
				$self->response->flush();

				$self->trigger("shutdown");
				ob_end_flush();
			});

			if (!ob_start("ob_gzhandler")) {
				ob_start();
			}

			$this->response = new Response();

			$this->trigger("before");
			$this->response->body = $this->dispatch();
		}

		return $this;
	}

	/**
	 * Завершить работу приложения (exit)
	 * @param mixed|bool $data
	 */
	public function terminate($data = false) {
		$this->exit = true;
		if ($data !== false) {
			echo $data;
		}
		exit;
	}

	/**
	 * Приложение завершено? (выключено)
	 * @return bool
	 */
	public function isTerminated() {
		return $this->exit;
	}

	/**
	 * Метод ссылка для метода bind объявляет Get роутинг
	 * @param      $path
	 * @param      $callback
	 * @param bool $condition
	 * @param int  $priority
	 * @return Orchid
	 */
	public function get($path, $callback, $condition = true, $priority = 0) {
		return $this->bind($path, $callback, "GET", $condition, $priority);
	}

	/**
	 * Метод ссылка для метода bind объявляет Post роутинг
	 * @param      $path
	 * @param      $callback
	 * @param bool $condition
	 * @param int  $priority
	 * @return Orchid
	 */
	public function post($path, $callback, $condition = true, $priority = 0) {
		return $this->bind($path, $callback, "POST", $condition, $priority);
	}

	/**
	 * Метод для объявления роутинга
	 * @param string  $path
	 * @param Closure $callback
	 * @param string  $method
	 * @param bool    $condition
	 * @param int     $priority
	 * @return Orchid
	 */
	public function bind($path, $callback, $method = null, $condition = true, $priority = 0) {
		if ((is_null($method) || $this->req_is($method)) && $condition) {
			if (is_object($callback) && $callback instanceof Closure) {
				$callback = $callback->bindTo($this, $this);
			}

			$this->registry["route"][] = [
				"path"     => $path,
				"callback" => $callback,
				"priority" => $priority,
			];
		}

		return $this;
	}

	/**
	 * Метод для привязки класса контроллера
	 * @param string $class
	 * @param bool   $alias
	 * @param null   $method
	 * @param bool   $condition
	 * @param int    $priority
	 * @return Orchid
	 */
	public function bindClass($class, $alias = false, $method = null, $condition = true, $priority = 0) {
		$self  = $this;
		$clean = $alias ? $alias : trim(strtolower(str_replace("\\", "/", $class)), "\\");

		$this->bind("/" . $clean . "/*", function () use ($self, $class, $clean) {
			$part   = explode("/", trim(str_replace($clean, "", $self->getSitePath()), "/"));
			$action = isset($part[0]) ? $part[0] : "index";
			$params = count($part) > 1 ? array_slice($part, 1) : [];

			return $self->invoke($class, $action, $params);
		}, $method, $condition, $priority);

		$this->bind("/" . $clean, function () use ($self, $class) {
			return $self->invoke($class, "index");
		}, $method, $condition, $priority);

		return $this;
	}

	/**
	 * Метод для перебора объявленных роутингов
	 * @return bool|mixed|null
	 */
	public function dispatch() {
		$param = [];
		$path  = "/" . implode("/", $this["uri"]);
		$found = false;
		if ($this->registry["route"]) {
			$queue = new SplPriorityQueue();
			foreach ($this->registry["route"] as $index => $action) {
				$queue->insert($index, $action["priority"]);
			}

			$queue->top();
			while ($queue->valid()) {
				$route = $this->registry["route"][$queue->current()];

				if ($route["path"] === $path) {
					$found = $this->route($route, $param);
					break;
				}

				/* #\.html$#  */
				if (substr($route["path"], 0, 1) == "#" && substr($route["path"], -1) == "#") {
					if (preg_match($route["path"], $path, $match)) {
						$param[":capture"] = array_slice($match, 1);
						$found             = $this->route($route, $param);
						break;
					}
				}

				/* /example/* */
				if (strpos($route["path"], "*") !== false) {
					$pattern = "#^" . str_replace("\\*", "(.*)", preg_quote($route["path"], "#")) . "#";
					if (preg_match($pattern, $path, $match)) {
						$param[":arg"] = array_slice($match, 1);
						$found         = $this->route($route, $param);
						break;
					}
				}

				/* /example/:id */
				if (strpos($route["path"], ":") !== false) {
					$part_p = explode("/", $route["path"]);
					array_shift($part_p);

					if (count($this["uri"]) == count($part_p)) {
						$matched = true;
						foreach ($part_p as $index => $part) {
							if (":" === substr($part, 0, 1)) {
								$param[substr($part, 1)] = $this["uri"][$index];
								continue;
							}
							if ($this["uri"][$index] != $part_p[$index]) {
								$matched = false;
								break;
							}
						}
						if ($matched) {
							$found = $this->route($route, $param);
							break;
						}
					}
				}

				$queue->next();
			}
		}

		return $found;
	}

	/**
	 * @param $route
	 * @param $param
	 * @return bool|mixed|null
	 */
	protected function route($route, $param) {
		$ret = null;

		if (is_callable($route["callback"])) {
			$ret = call_user_func($route["callback"], $param);
		}

		return !is_null($ret) ? $ret : false;
	}

	/**
	 * Перенаправляет на адрес
	 * @param  string $path
	 * @return void
	 */
	public function reroute($path) {
		if (strpos($path, "://") === false) {
			if (substr($path, 0, 1) != "/") {
				$path = "/" . $path;
			}
			$path = $this->routeUrl($path);
		}

		header("Location: " . $path);
		$this->terminate();
	}

	/**
	 * Возвращает ссылку
	 * @param  string $path
	 * @return string
	 */
	public function routeUrl($path) {
		return $this->getSiteUrl(false) . "/" . ltrim($path, "/");
	}

	/**
	 * Метод помощник по работе с путями
	 * @param $args
	 * @return string
	 */
	public function path(...$args) {
		switch (count($args)) {
			case 1:
				$file = $args[0];

				if ($this->isAbsolutePath($file) && file_exists($file)) {
					return $file;
				}

				if (($parts = explode(":", $file, 2)) && count($parts) == 2) {
					if (!isset($this->registry["path"][$parts[0]])) {
						return false;
					}

					foreach ($this->registry["path"][$parts[0]] as &$path) {
						if (file_exists($path . $parts[1])) {
							return $path . $parts[1];
						}
					}
				}

				return false;
			case 2:
				list($name, $path) = $args;
				if (!isset($this->registry["path"][$name])) {
					$this->registry["path"][$name] = [];
				}
				$path = str_replace(DIRECTORY_SEPARATOR, "/", $path);
				array_unshift($this->registry["path"][$name], is_file($path) ? $path : $path . "/");

				break;
		}

		return false;
	}

	/**
	 * Функция помощник по работе с путями
	 * @param string $path путь до файла
	 * @return bool
	 */
	public function isAbsolutePath($path) {
		return "/" == $path[0] || "\\" == $path[0] || (3 < strlen($path) && ctype_alpha($path[0]) && $path[1] == ":" && ("\\" == $path[2] || "/" == $path[2]));
	}

	/**
	 * Метод преобразует путь в ссылку
	 * @param string $path путь до файла
	 * @return string|bool
	 */
	public function pathToUrl($path) {
		if (($file = $this->path($path)) != false) {
			$file = str_replace(DIRECTORY_SEPARATOR, "/", $file);
			$root = str_replace(DIRECTORY_SEPARATOR, "/", $this["base_dir"]);

			return "/" . ltrim(str_replace($root, "", $file), "/");
		}

		return false;
	}

	/**
	 * Загружает модули из переданных директорий
	 * @param array $dirs
	 * @return Orchid
	 */
	public function loadModule(array $dirs) {
		foreach ($dirs as &$dir) {
			if (is_dir($dir)) {
				foreach (new DirectoryIterator($dir) as $module) {
					if ($module->isDir() && !$module->isDot() || $module->isFile()) {
						$this->addModule($module->getBasename(".php"), $module->getRealPath());
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Метод добавляет модуль в реестр и загружает
	 * @param $name
	 * @param $dir
	 * @return Module
	 */
	public function addModule($name, $dir) {
		if (!isset($this->registry["module"][strtolower($name)])) {
			$this->path($name, $dir);
			$this->registry["module"][strtolower($name)] = $this->bootModule($name, $dir);
		}

		return $this->registry["module"][strtolower($name)];
	}

	/**
	 * Подгружает файл модуля
	 * @param $class
	 * @param $dir
	 * @return Module
	 */
	protected function bootModule($class, $dir) {
		if (is_file($dir)) {
			require_once($dir);
		} else {
			$class = "Module" . $class;
			require_once($dir . DIRECTORY_SEPARATOR . $class . ".php");
		}

		return new $class();
	}

	/**
	 * @param $name
	 * @return Module|null
	 */
	public function module($name) {
		$name = strtolower($name);

		return isset($this->registry["module"][$name]) ? $this->registry["module"][$name] : null;
	}

	/**
	 * Исполняет демона
	 * @return void
	 */
	protected function bootDaemon() {
		$class = "Engine\\Daemon\\" . $this["args"][0];

		new $class();
	}

	/**
	 * Запускает выполнение демона в фоне
	 * @param array $args первый аргумент - имя класса демона
	 * @return void
	 */
	public function runDaemon(...$args) {
		system("php " . $this["base_dir"] . DIRECTORY_SEPARATOR . "index.php " . implode(" ", $args) . " > /dev/null &");
	}

	/**
	 * Создаёт замыкание, доступ по ключу
	 * @param string  $name     название сервиса
	 * @param Closure $callable замыкание
	 * @return object
	 */
	public function service($name, $callable) {
		$this[$name] = function ($param = null) use ($callable) {
			static $object;

			if (is_object($callable) && $callable instanceof Closure) {
				$callable = $callable->bindTo($this, $this);
			}

			if ($object === null) {
				$object = $callable($param);
			}

			return $object;
		};
	}

	/**
	 * Метод возвращает объект расширения
	 * @param string $extension замыкание
	 * @return Extension
	 */
	public function extension($extension) {
		if (!isset($this->registry["extension"][$extension])) {
			$class                                   = "Engine\\Extension\\" . $extension;
			$this->registry["extension"][$extension] = new $class();
		}

		return $this->registry["extension"][$extension];
	}

	/**
	 * Вызов класса контроллера
	 * @param  string $class  имя контроллера
	 * @param  string $action метод
	 * @param  array  $params параметры вызова
	 * @return mixed
	 */
	public function invoke($class, $action = "index", array $params = []) {
		$controller = new $class();

		return call_user_func_array([$controller, $action], $params);
	}

	/**
	 * Метод для отрисовки шаблонов
	 * @param string $_template абсолютный или ссылочный путь может содержать операторы
	 *                          "->"    указывает что шаблон слева необходимо поместить в шаблон справа
	 *                          ";"     разделитель шаблонов в левой части
	 * @param array  $_vars массив с переменными
	 * @return bool|mixed
	 */
	public function render($_template, array $_vars = []) {
		$content = [];
		extract($_vars, EXTR_REFS);

		if (strpos($_template, "->") !== false) {
			list($_template, $_layout) = array_map("trim", explode("->", $_template, 2));
		} else {
			// если передан только один шаблон
			$_layout   = $_template;
			$_template = false;
		}
		if ($_template) {
			if (strpos($_template, ";") !== false) {
				$_template = array_map("trim", explode(";", $_template));
			} else {
				// если шаблон только один
				$_template = [$_template];
			}

			// рендерим дополнительные шаблоны
			foreach ($_template as $val) {
				if ($_file = $this->path($val)) {
					ob_start();
					require $_file;
					$content[basename($_file, ".php")] = ob_get_clean();
				}
			}
		}

		// рендерим шаблон
		if ($_layout && $_file = $this->path($_layout)) {
			ob_start();
			require $_file;
			$content = ob_get_clean();
		}

		return $content ? $content : false;
	}

	// ArrayAccess
	public function offsetSet($key, $value) {
		$this->registry[$key] = $value;
	}

	public function offsetGet($key) {
		$value = $this->retrieve($key, "key-not-found");
		if ($value !== "key-not-found") {
			return $value instanceof Closure ? $value($this) : $value;
		}
		throw new InvalidArgumentException(sprintf("Id '%s' not found.", $key));
	}

	public function offsetExists($key) {
		return isset($this->registry[$key]);
	}

	public function offsetUnset($key) {
		unset($this->registry[$key]);
	}

	/**
	 * Для доступа к расширениям
	 * @param $extension
	 * @return Extension
	 */
	public function __invoke($extension) {
		return $this->extension($extension);
	}

	/**
	 * Читает значение из реестра
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key) {
		return $this->retrieve($key);
	}

	/**
	 * Читает значение из реестра
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public function retrieve($key, $default = null) {
		return fetch_from_array($this->registry, $key, $default);
	}

	/**
	 * Записывает значение в реестр
	 * @param string $key   ключ
	 * @param mixed  $value значение
	 * @return Orchid
	 */
	public function __set($key, $value) {
		$keys = explode("/", $key);
		if (count($keys) > 5) {
			return false;
		}
		switch (count($keys)) {
			case 1:
				$this->registry[$keys[0]] = $value;
				break;
			case 2:
				$this->registry[$keys[0]][$keys[1]] = $value;
				break;
			case 3:
				$this->registry[$keys[0]][$keys[1]][$keys[2]] = $value;
				break;
			case 4:
				$this->registry[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
				break;
			case 5:
				$this->registry[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
				break;
		}

		return $this;
	}
}

function fetch_from_array(&$array, $index = null, $default = null) {
	if (is_null($index)) {
		return $array;
	} elseif (isset($array[$index])) {
		return $array[$index];
	} elseif (strpos($index, "/")) {
		$keys = explode("/", $index);
		switch (count($keys)) {
			case 1:
				if (isset($array[$keys[0]])) {
					return $array[$keys[0]];
				}
				break;
			case 2:
				if (isset($array[$keys[0]][$keys[1]])) {
					return $array[$keys[0]][$keys[1]];
				}
				break;
			case 3:
				if (isset($array[$keys[0]][$keys[1]][$keys[2]])) {
					return $array[$keys[0]][$keys[1]][$keys[2]];
				}
				break;
			case 4:
				if (isset($array[$keys[0]][$keys[1]][$keys[2]][$keys[3]])) {
					return $array[$keys[0]][$keys[1]][$keys[2]][$keys[3]];
				}
				break;
		}
	}

	return $default;
}
