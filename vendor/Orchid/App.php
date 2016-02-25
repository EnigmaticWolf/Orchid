<?php

namespace Orchid;

use Closure;
use DirectoryIterator;

class App {
	protected static $registry = [];
	protected static $exit     = false;

	public static function initialize(array $param = []) {
		static::$registry = array_merge([
			"debug"     => true,

			"instance"  => [],
			"app"       => "public",

			"secret"    => "secret",
			"session"   => "session",
			"locale"    => [],

			"autoload"  => [],
			"module"    => [],
			"path"      => [],

			"host"      => isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "",
			"method"    => isset($_SERVER["REQUEST_METHOD"]) ? $_SERVER["REQUEST_METHOD"] : "",
			"uri"       => [],
			"param"     => [],
			"data"      => [],
			"args"      => PHP_SAPI == "cli" ? array_slice($_SERVER["argv"], 1) : [],

			"base_dir"  => isset($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : "",
			"base_host" => isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "",
			"base_port" => (int)(isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80),
		], $param);

		// дополнительный загрузшик
		spl_autoload_register(function ($class) {
			foreach (App::retrieve("autoload", []) as $dir) {
				$class_path = $dir . "/" . str_replace(["\\", "_"], "/", $class) . ".php";

				if (file_exists($class_path)) {
					require_once($class_path);

					return;
				}
			}
		});

		// декодируем строку запроса
		$_SERVER["REQUEST_URI"] = urldecode(isset($_SERVER["REQUEST_URI"]) ? $_SERVER["REQUEST_URI"] : "");

		// заполняем URI
		foreach (explode("/", parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH)) as $part) {
			if ($part) {
				static::$registry["uri"][] = $part;
			}
		}

		// переписываем GET
		$_GET = [];
		foreach (explode("&", parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY)) as $part) {
			if ($part) {
				$data                                = explode("=", $part);
				static::$registry["param"][$data[0]] = $_GET[$data[0]] = isset($data[1]) ? $data[1] : "";
			}
		}

		// проверяем php://input и объединяем с $_POST
		if (static::req_is("ajax")) {
			if ($json = json_decode(@file_get_contents("php://input"), true)) {
				$_POST = array_merge($_POST, $json);
			}
		}

		static::$registry["data"] = $_POST;
		$_REQUEST                 = array_merge($_GET, $_POST, $_COOKIE);
	}

	/**
	 * Приложение завершено? (выключено)
	 * @return bool
	 */
	public static function isTerminated() {
		return static::$exit;
	}

	/**
	 * Завершить работу приложения (exit)
	 * @param mixed|bool $message
	 */
	public static function terminate($message = false) {
		static::$exit = true;

		ob_clean();

		if ($message !== false) {
			echo $message;
		}

		exit;
	}

	/**
	 * @param $type
	 * @return bool|int
	 */
	public static function req_is($type) {
		switch (strtolower($type)) {
			case "ajax": {
				return (
					(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") ||
					(isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false) ||
					(isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") !== false)
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
				return (strtolower(static::$registry["method"]) == "head");
			}

			case "put": {
				return (strtolower(static::$registry["method"]) == "put");
			}

			case "post": {
				return (strtolower(static::$registry["method"]) == "post");
			}

			case "get": {
				return (strtolower(static::$registry["method"]) == "get");
			}

			case "delete": {
				return (strtolower(static::$registry["method"]) == "delete");
			}

			case "options": {
				return (strtolower(static::$registry["method"]) == "options");
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
	public static function getClientIp() {
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
	 * Возвращает наиболее подходящий язык клиента из заданных в locale
	 * @param string $default по умолчанию русский
	 * @return string
	 */
	public static function getClientLang($default = "ru") {
		if (($list = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]))) {
			if (preg_match_all("/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/", $list, $list)) {
				$language = [];

				foreach (array_combine($list[1], $list[2]) as $lang => $priority) {
					$language[$lang] = (float)($priority ? $priority : 1);
				}
				arsort($language, SORT_NUMERIC);

				foreach ($language as $lang => $priority) {
					if (in_array($lang, static::$registry["locale"])) {
						return $lang;
					}
				}
			}
		}

		return $default;
	}

	/**
	 * Возвращает адрес сайта
	 * @param bool   $withPath
	 * @param string $app
	 * @return string
	 */
	public static function getSiteUrl($withPath = false, $app = "") {
		$url = (static::req_is("ssl") ? "https" : "http") . "://";
		if ($app = (empty($app) && static::$registry["app"] != "public") ? static::$registry["app"] : $app) {
			$url .= $app . ".";
		}
		$url .= static::$registry["base_host"];
		if (static::$registry["base_port"] != 80) {
			$url .= ":" . static::$registry["base_port"];
		}
		if ($withPath) {
			$url .= static::getSitePath();
		}

		return rtrim($url, "/");
	}

	/**
	 * Возвращает путь
	 * @return string
	 */
	public static function getSitePath() {
		return "/" . implode("/", static::$registry["uri"]);
	}

	/**
	 * Загружает модули из переданных директорий
	 * @param array $dirs
	 */
	public static function loadModule(array $dirs) {
		foreach ($dirs as &$dir) {
			if (is_dir($dir)) {
				foreach (new DirectoryIterator($dir) as $module) {
					if ($module->isDir() && !$module->isDot() || $module->isFile() && $module->getExtension() == "php") {
						static::bootModule($module->getBasename(".php"), $module->getRealPath());
					}
				}

				// регистрируем папку автозагрузки
				static::$registry["autoload"][] = $dir;
			}
		}
	}

	/**
	 * Подгружает файл модуля и инициализирует его
	 * @param $class
	 * @param $dir
	 */
	protected static function bootModule($class, $dir) {
		// регистрируем модуль
		static::$registry["module"][] = $class;

		if (!is_file($dir)) {
			static::path($class, $dir);

			$class = "Module" . $class;
			$dir = $dir . DIRECTORY_SEPARATOR . $class . ".php";
		}

		if (file_exists($dir)) {
			require_once($dir);
		}

		call_user_func([$class, "initialize"]);
	}

	/**
	 * Запуск приложения
	 */
	public static function run() {
		register_shutdown_function(function () {
			// если приложение было завершено
			if (App::isTerminated()) {
				return;
			}

			$error = error_get_last();
			if ($error && in_array($error["type"], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_USER_ERROR])) {
				ob_end_clean();
				Response::$nocache = true;
				Response::$status  = "500";
				Response::$body    = App::retrieve("debug", false) ? $error : "Internal Error.";
			} elseif (!Response::$body) {
				Response::$nocache = true;
				Response::$status  = "404";
				Response::$body    = "Path not found.";
			}

			Task::trigger("after");
			Response::flush();

			Task::trigger("shutdown");
			ob_end_flush();
		});

		ob_start();

		Task::trigger("before");
		Response::$body = Router::dispatch();
	}

	/**
	 * Метод помощник по работе с путями
	 * @param $args
	 * @return string
	 */
	public static function path(...$args) {
		switch (count($args)) {
			case 1:
				$file = $args[0];

				if (static::isAbsolutePath($file) && file_exists($file)) {
					return $file;
				}

				if (($parts = explode(":", $file, 2)) && count($parts) == 2) {
					if (!isset(static::$registry["path"][$parts[0]])) {
						return false;
					}

					foreach (static::$registry["path"][$parts[0]] as &$path) {
						if (file_exists($path . $parts[1])) {
							return $path . $parts[1];
						}
					}
				}

				return false;
			case 2:
				list($name, $path) = $args;
				if (!isset(static::$registry["path"][$name])) {
					static::$registry["path"][$name] = [];
				}
				$path = str_replace(DIRECTORY_SEPARATOR, "/", $path);
				array_unshift(static::$registry["path"][$name], is_file($path) ? $path : $path . "/");

				break;
		}

		return false;
	}

	/**
	 * Функция помощник по работе с путями
	 * @param string $path путь до файла
	 * @return bool
	 */
	public static function isAbsolutePath($path) {
		return $path && ("/" == $path[0] || "\\" == $path[0] || (3 < strlen($path) && ctype_alpha($path[0]) && $path[1] == ":" && ("\\" == $path[2] || "/" == $path[2])));
	}

	/**
	 * Метод преобразует путь в ссылку
	 * @param string $path путь до файла
	 * @return string|bool
	 */
	public static function pathToUrl($path) {
		if (($file = static::path($path)) != false) {
			$file = str_replace(DIRECTORY_SEPARATOR, "/", $file);
			$root = str_replace(DIRECTORY_SEPARATOR, "/", static::$registry["base_dir"]);

			return "/" . ltrim(str_replace($root, "", $file), "/");
		}

		return false;
	}

	/**
	 * Вызов класса контроллера
	 * @param  string $controller имя контроллера
	 * @param  string $action     метод
	 * @param  array  $params     параметры вызова
	 * @return mixed
	 */
	public static function invoke($controller, $action = "index", array $params = []) {
		return method_exists($controller, $action) ? call_user_func_array([$controller, $action], $params) : false;
	}

	/**
	 * Метод для отрисовки шаблонов
	 * @param string $_template абсолютный или ссылочный путь может содержать операторы
	 *                          "->"    указывает что шаблон слева необходимо поместить в шаблон справа
	 *                          ";"     разделитель шаблонов в левой части
	 * @param array  $_vars     массив с переменными
	 * @return bool|mixed
	 */
	public static function render($_template, array $_vars = []) {
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
				if ($_file = static::path($val)) {
					ob_start();
					require $_file;
					$content[basename($_file, ".php")] = ob_get_clean();
				}
			}
		}

		// рендерим шаблон
		if ($_layout && $_file = static::path($_layout)) {
			ob_start();
			require $_file;
			$content = ob_get_clean();
		}

		return $content ? $content : false;
	}

	/**
	 * Создаёт замыкание
	 * @param string  $name     название сервиса
	 * @param Closure $callable замыкание
	 * @return bool
	 */
	public static function addService($name, $callable) {
		return static::set($name, function ($param = null) use ($callable) {
			static $object;

			if ($object === null) {
				$object = $callable($param);
			}

			return $object;
		});
	}

	/**
	 * Записывает значение в реестр
	 * @param string $key   ключ
	 * @param mixed  $value значение
	 * @return bool
	 */
	public static function set($key, $value) {
		$keys = explode("/", $key);
		if (count($keys) > 5) {
			return false;
		}
		switch (count($keys)) {
			case 1:
				static::$registry[$keys[0]] = $value;
				break;
			case 2:
				static::$registry[$keys[0]][$keys[1]] = $value;
				break;
			case 3:
				static::$registry[$keys[0]][$keys[1]][$keys[2]] = $value;
				break;
			case 4:
				static::$registry[$keys[0]][$keys[1]][$keys[2]][$keys[3]] = $value;
				break;
			case 5:
				static::$registry[$keys[0]][$keys[1]][$keys[2]][$keys[3]][$keys[4]] = $value;
				break;
		}

		return true;
	}

	/**
	 * Читает значение из реестра
	 * @param string $key
	 * @return mixed
	 */
	public static function get($key) {
		return static::retrieve($key);
	}

	/**
	 * Читает значение из реестра
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public static function retrieve($key, $default = null) {
		$value = fetch_from_array(static::$registry, $key, $default);

		return $value instanceof Closure ? $value() : $value;
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
