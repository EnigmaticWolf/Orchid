<?php

namespace Orchid;

use DirectoryIterator;

class App {
	protected static $registry = [];
	protected static $exit     = false;

	/**
	 * Инициализатор приложения
	 *
	 * @param array $param
	 *
	 * @return void
	 */
	public static function initialize(array $param = []) {
		Registry::setAll(array_merge([
			"debug"     => true,

			"instance"  => [],
			"app"       => "public",
			"locale"    => [],

			"secret"    => "secret",

			"autoload"  => [],
			"module"    => [],
			"path"      => [],

			"args"      => [],

			"base_dir"  => isset($_SERVER["DOCUMENT_ROOT"]) ? $_SERVER["DOCUMENT_ROOT"] : "",
			"base_host" => isset($_SERVER["HTTP_HOST"]) ? $_SERVER["HTTP_HOST"] : "",
			"base_port" => (int)(isset($_SERVER["SERVER_PORT"]) ? $_SERVER["SERVER_PORT"] : 80),
		], $param));

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
		} else {
			Registry::set("args", array_slice($_SERVER["argv"], 1));
		}

		// дополнительный загрузшик
		spl_autoload_register(function ($class) {
			foreach (Registry::get("autoload", []) as $dir) {
				$class_path = $dir . "/" . str_replace(["\\", "_"], "/", $class) . ".php";

				if (file_exists($class_path)) {
					require_once($class_path);

					return;
				}
			}
		});
	}

	/**
	 * Приложение завершено? (выключено)
	 *
	 * @return bool
	 */
	public static function isTerminated() {
		return static::$exit;
	}

	/**
	 * Завершить работу приложения (exit)
	 *
	 * @param mixed|bool $message
	 */
	public static function terminate($message = false) {
		static::$exit = true;

		ob_clean();

		if ($message !== false) {
			echo $message;
		}
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
				Registry::add("autoload", $dir);
			}
		}
	}

	/**
	 * Подгружает файл модуля и инициализирует его
	 *
	 * @param $class
	 * @param $dir
	 */
	protected static function bootModule($class, $dir) {
		// регистрируем модуль
		Registry::add("module", $class);

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
				Response::setStatus(Response::HTTP_BAD_GATEWAY);
				Response::setContent();
			}

			$error = error_get_last();
			if ($error && in_array($error["type"], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_USER_ERROR])) {
				ob_end_clean();
				Response::setStatus(Response::HTTP_INTERNAL_SERVER_ERROR);
				Response::setContent(Registry::get("debug") ? $error : "Internal Error.");
			} elseif (!Response::getContent()) {
				Response::setStatus(Response::HTTP_NOT_FOUND);
				Response::setContent("Path not found.");
			}

			Task::trigger("shutdown");
			Response::send();
		});

		ob_start("ob_gzhandler");
		ob_implicit_flush(false);

		Response::create();
		Task::trigger("before");

		Response::setContent(Router::dispatch());
		Task::trigger("after");
	}

	/**
	 * Метод помощник по работе с путями
	 *
	 * @param $args
	 *
	 * @return string
	 * todo:: переписать
	 */
	public static function path(...$args) {
		switch (count($args)) {
			case 1:
				$file = $args[0];

				if (static::isAbsolutePath($file) && file_exists($file)) {
					return $file;
				}

				if (($parts = explode(":", $file, 2)) && count($parts) == 2) {
					$pathList = Registry::get("path", []);
					if (!isset($pathList[$parts[0]])) {
						return false;
					}

					foreach ($pathList[$parts[0]] as &$path) {
						if (file_exists($path . $parts[1])) {
							return $path . $parts[1];
						}
					}
				}

				return false;
			case 2:
				$pathList = &Registry::get("path", []);
				list($name, $path) = $args;
				if (!isset($pathList[$name])) {
					$pathList[$name] = [];
				}
				$path = str_replace(DIRECTORY_SEPARATOR, "/", $path);
				array_unshift($pathList[$name], is_file($path) ? $path : $path . "/");

				break;
		}

		return false;
	}

	/**
	 * Функция помощник по работе с путями
	 *
	 * @param string $path путь до файла
	 *
	 * @return bool
	 */
	public static function isAbsolutePath($path) {
		return $path && ("/" == $path[0] || "\\" == $path[0] || (3 < strlen($path) && ctype_alpha($path[0]) && $path[1] == ":" && ("\\" == $path[2] || "/" == $path[2])));
	}

	/**
	 * Метод преобразует путь в ссылку
	 *
	 * @param string $path путь до файла
	 *
	 * @return string|bool
	 */
	public static function pathToUrl($path) {
		if (($file = static::path($path)) != false) {
			return "/" . ltrim(str_replace(Registry::get("base_dir"), "", $file), "/");
		}

		return false;
	}
}
