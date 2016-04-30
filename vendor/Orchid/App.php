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
	 * Режим отладки
	 *
	 * @var bool
	 */
	protected static $debug = true;

	/**
	 * Список возможных приложений
	 *
	 * @var array
	 */
	protected static $instance = [];

	/**
	 * Активное приложение
	 *
	 * @var string
	 */
	protected static $app = "public";

	/**
	 * Возможные языки приложения
	 *
	 * @var array
	 */
	protected static $locale = [];

	/**
	 * Хэш-соль
	 *
	 * @var string
	 */
	protected static $secret = "secret";

	/**
	 * Подключенные модули
	 *
	 * @var array
	 */
	protected static $module = [];

	/**
	 * Пути папок модулей для автозагрузки
	 *
	 * @var array
	 */
	protected static $autoload = [];

	/**
	 * Пути приложения
	 *
	 * @var array
	 */
	protected static $path = [];

	/**
	 * Аргументы переданные скрипту
	 *
	 * @var array
	 */
	public static $args = [];

	/**
	 * Базовая директория приложения
	 *
	 * @var string
	 */
	protected static $base_dir = null;

	/**
	 * Базовое имя хоста
	 *
	 * @var string
	 */
	protected static $base_host = null;

	/**
	 * Базовый порт
	 *
	 * @var int
	 */
	protected static $base_port = 80;

	/**
	 * Завершено ли приложение
	 *
	 * @var bool
	 */
	protected static $exit = false;

	/**
	 * Инициализатор приложения
	 *
	 * @param array $param
	 *
	 * @return void
	 */
	public static function initialize(array $param = []) {
		if (isset($param["debug"])) {
			static::$debug = $param["debug"];
		}

		if (isset($param["instance"])) {
			static::$instance = $param["instance"];
		}

		if (isset($param["app"])) {
			static::$app = $param["app"];
		}

		if (isset($param["locale"])) {
			static::$locale = $param["locale"];
		}

		if (isset($param["secret"])) {
			static::$secret = $param["secret"];
		}

		if (isset($param["base_dir"])) {
			static::$base_dir = $param["base_dir"];
		} else {
			if (!empty($_SERVER["DOCUMENT_ROOT"])) {
				static::$base_dir = $_SERVER["DOCUMENT_ROOT"];
			} elseif (defined("ORCHID")) {
				static::$base_dir = ORCHID;
			}
		}

		if (isset($param["base_host"])) {
			static::$base_host = $param["base_host"];
		} else {
			if (isset($_SERVER["HTTP_HOST"])) {
				static::$base_host = $_SERVER["HTTP_HOST"];
			}
		}

		if (isset($param["base_port"])) {
			static::$base_port = $param["base_port"];
		} else {
			if (isset($_SERVER["SERVER_PORT"])) {
				static::$base_port = $_SERVER["SERVER_PORT"];
			}
		}

		// дополнительный загрузшик
		spl_autoload_register(function ($class) {
			foreach (static::$autoload as $dir) {
				$class_path = $dir . "/" . str_replace(["\\", "_"], "/", $class) . ".php";

				if (file_exists($class_path)) {
					require_once($class_path);

					return;
				}
			}
		});

		if (PHP_SAPI != "cli") {
			// инициализиуем запрос
			Request::initialize(
				$_SERVER["REQUEST_SCHEME"] . "://" . $_SERVER['HTTP_HOST'] . ($_SERVER["SERVER_PORT"] == 80 ? "" : $_SERVER["SERVER_PORT"]) . $_SERVER['REQUEST_URI'],
				$_SERVER["REQUEST_METHOD"],
				$_POST,
				$_FILES,
				$_COOKIE,
				(isset($_SESSION) ? $_SESSION : [])
			);

			// обработка исключений
			set_exception_handler(function (Exception $ex) {
				ob_end_clean();

				if (static::$debug) {
					$message = "Exception: " . $ex->getMessage() . " (code " . $ex->getCode() . ")\nFile: " . $ex->getFile() . " (at " . $ex->getLine() . " line)\nTrace:\n" . $ex->getTraceAsString();
				} else {
					$message = "Internal Error";
				}

				Response::create($message, Response::HTTP_INTERNAL_SERVER_ERROR, "txt");
			});

			// обработка заверщения работы
			register_shutdown_function(function () {
				if (($error = error_get_last()) && error_reporting() & $error["type"]) {
					ob_end_clean();

					if (static::$debug) {
						$message = "ERROR: " . $error["message"] . " (code " . $error["type"] . ")\nFile: " . $error["file"] . " (at " . $error["line"] . " line)";
					} else {
						$message = "Internal Error";
					}

					Response::create($message, Response::HTTP_INTERNAL_SERVER_ERROR, "txt");
				} else {
					if (Response::isOk() && !Response::getContent()) {
						Response::setStatus(Response::HTTP_NOT_FOUND);
						Response::setContent("Path not found");
					}
				}

				Task::trigger("shutdown");
				Response::send();
			});
		} else {
			static::$args = array_slice($_SERVER["argv"], 1);
		}
	}

	/**
	 * Включен ли режим отладки
	 *
	 * @return bool
	 */
	public static function isDebug() {
		return static::$debug;
	}

	/**
	 * Возвращает массив возможных приложений
	 *
	 * @return array
	 */
	public static function getInstanceList() {
		return static::$instance;
	}

	/**
	 * Устанавливает текущее приложение
	 *
	 * @param $app
	 */
	public static function setApp($app) {
		static::$app = $app;
	}

	/**
	 * Возвращает текущее приложение
	 *
	 * @return string
	 */
	public static function getApp() {
		return static::$app;
	}

	/**
	 * Возвращает массив возможных языков приложения
	 *
	 * @return string
	 */
	public static function getLocaleList() {
		return static::$locale;
	}

	/**
	 * Возвращает хэш-соль
	 *
	 * @return string
	 */
	public static function getSecret() {
		return static::$secret;
	}

	/**
	 * Возвращает массив подключенных модулей
	 *
	 * @return array
	 */
	public static function getModuleList() {
		return static::$module;
	}

	/**
	 * Возвращает массив папок модулей
	 *
	 * @return array
	 */
	public static function getAutoloadList() {
		return static::$autoload;
	}

	/**
	 * Функция помощник по работе с путями
	 *
	 * @param string $path путь до файла
	 *
	 * @return bool
	 */
	public static function isAbsolutePath($path) {
		return $path && ("/" == $path[0] || "\\" == $path[0] || (3 < mb_strlen($path) && ctype_alpha($path[0]) && $path[1] == ":" && ("\\" == $path[2] || "/" == $path[2])));
	}

	/**
	 * Добавление короткой ссылки для пути до каталога или файла
	 *
	 * @param $shortcut
	 * @param $path
	 *
	 * @return int
	 */
	public static function addPath($shortcut, $path) {
		$path = str_replace(DIRECTORY_SEPARATOR, "/", $path);

		if (!isset(static::$path[$shortcut])) {
			static::$path[$shortcut] = [];
		}

		return array_unshift(static::$path[$shortcut], is_file($path) ? $path : $path . "/");
	}

	/**
	 * Получение пути до каталога или файла
	 * Пример: view:template.php
	 *
	 * @param $path
	 *
	 * @return bool|string
	 */
	public static function getPath($path) {
		if (static::isAbsolutePath($path) && file_exists($path)) {
			return $path;
		}

		if (($parts = explode(":", $path, 2)) && count($parts) == 2) {
			if (isset(static::$path[$parts[0]])) {
				foreach (static::$path[$parts[0]] as &$path) {
					if (file_exists($path . $parts[1])) {
						return $path . $parts[1];
					}
				}
			}
		}

		return false;
	}

	/**
	 * Метод преобразует путь в ссылку
	 *
	 * @param string $path путь до файла
	 *
	 * @return string|bool
	 */
	public static function pathToUrl($path) {
		if (($file = static::getPath($path)) != false) {
			return "/" . ltrim(str_replace(static::$base_dir, "", $file), "/");
		}

		return false;
	}

	/**
	 * Возвращает массив путей
	 *
	 * @return array
	 */
	public static function getPathList() {
		return static::$path;
	}

	/**
	 * Возвращает массив путей по имени
	 *
	 * @param $name
	 *
	 * @return array
	 */
	public static function getPathListByName($name) {
		return isset(static::$path[$name]) ? static::$path[$name] : [];
	}

	/**
	 * Возвращает базовую директорию
	 *
	 * @return string
	 */
	public static function getBaseDir() {
		return static::$base_dir;
	}

	/**
	 * Возвращает базовый хост
	 *
	 * @return string
	 */
	public static function getBaseHost() {
		return static::$base_host;
	}

	/**
	 * Возвращает базовый порт
	 *
	 * @return int
	 */
	public static function getBasePort() {
		return static::$base_port;
	}

	/**
	 * Загружает модули из переданных директорий
	 *
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
				static::$autoload[] = $dir;
			}
		}
	}

	/**
	 * Подгружает файл модуля и инициализирует его
	 *
	 * @param $class
	 * @param $dir
	 *
	 * @return mixed
	 * @throws FileNotFoundException
	 * @throws NoSuchMethodException
	 */
	protected static function bootModule($class, $dir) {
		// регистрируем модуль
		static::$module[] = $class;

		if (!is_file($dir)) {
			static::addPath($class, $dir);

			$class = "Module" . $class;
			$dir = $dir . DIRECTORY_SEPARATOR . $class . ".php";
		}

		if (file_exists($dir)) {
			require_once($dir);
		} else {
			throw new FileNotFoundException("Не удалось найти указанный файл");
		}

		if (method_exists($class, "initialize")) {
			return call_user_func([$class, "initialize"]);
		}

		throw new NoSuchMethodException("Метод initialize не найден в классе " . $class);
	}

	/**
	 * Запуск приложения
	 */
	public static function run() {
		ob_start("ob_gzhandler");
		ob_implicit_flush(false);

		Task::trigger("before");
		Response::create(Router::dispatch());
		Task::trigger("after");
	}

	/**
	 * Хранилище сервисов
	 * @var array
	 */
	protected static $closures = [];

	/**
	 * Добавляет замыкание
	 *
	 * @param string  $name
	 * @param Closure $callable
	 *
	 * @return bool
	 * @throws RuntimeException
	 */
	public static function addClosure($name, $callable) {
		if (is_string($name) && !isset(static::$closures[$name])) {
			static::$closures[$name] = function ($param = null) use ($callable) {
				static $object;

				if ($object === null) {
					$object = $callable($param);
				}

				return $object;
			};

			return true;
		}

		throw new RuntimeException("Не удалось добавить замыкание " . $name);
	}

	/**
	 * Возвращает результат работы замыкания
	 *
	 * @param string $name
	 * @param array  ...$param
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	public static function getClosure($name, ...$param) {
		if (is_string($name) && array_key_exists($name, static::$closures) && is_callable(static::$closures[$name])) {
			return call_user_func_array(static::$closures[$name], $param);
		}

		throw new RuntimeException("Не удалось выполнить замыкание " . $name);
	}
}
