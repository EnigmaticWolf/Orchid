<?php

namespace Orchid;

use Closure;
use SplPriorityQueue;

class Router {
	/**
	 * Хранилище объявленных роутов
	 *
	 * @var array
	 */
	protected static $route = [];

	/**
	 * Метод ссылка для метода bind объявляет Get роутинг
	 *
	 * @param      $path
	 * @param      $callback
	 * @param bool $condition
	 * @param int  $priority
	 */
	public static function get($path, $callback, $condition = true, $priority = 0) {
		static::bind($path, $callback, "GET", $condition, $priority);
	}

	/**
	 * Метод ссылка для метода bind объявляет Post роутинг
	 *
	 * @param      $path
	 * @param      $callback
	 * @param bool $condition
	 * @param int  $priority
	 */
	public static function post($path, $callback, $condition = true, $priority = 0) {
		static::bind($path, $callback, "POST", $condition, $priority);
	}

	/**
	 * Метод для объявления роутинга
	 *
	 * @param string  $path
	 * @param Closure $callback
	 * @param string  $method
	 * @param bool    $condition
	 * @param int     $priority
	 */
	public static function bind($path, $callback, $method = null, $condition = true, $priority = 0) {
		if ((is_null($method) || Request::is($method)) && $condition) {
			static::$route[] = [
				"path"     => $path,
				"callback" => $callback,
				"priority" => $priority,
			];
		}
	}

	/**
	 * Метод для привязки класса контроллера
	 *
	 * @param string $class
	 * @param bool   $alias
	 * @param null   $method
	 * @param bool   $condition
	 * @param int    $priority
	 */
	public static function bindClass($class, $alias = false, $method = null, $condition = true, $priority = 0) {
		$clean = $alias ? $alias : trim(strtolower(str_replace("\\", "/", $class)), "\\");

		static::bind("/" . $clean . "/*", function () use ($class, $clean) {
			$part = explode("/", trim(str_replace($clean, "", Request::getPath()), "/"));
			$action = isset($part[0]) ? $part[0] : "index";
			$params = count($part) > 1 ? array_slice($part, 1) : [];

			return call_user_func_array([$class, $action], $params);
		}, $method, $condition, $priority);

		static::bind("/" . $clean, function () use ($class) {
			return call_user_func([$class, "index"]);
		}, $method, $condition, $priority);
	}

	/**
	 * Метод для перебора объявленных роутингов
	 *
	 * @return mixed|false
	 * todo:: оптимизировать
	 */
	public static function dispatch() {
		$param = [];
		$path = "/" . implode("/", Request::getUriList());
		$found = false;
		if (static::$route) {
			$queue = new SplPriorityQueue();
			foreach (static::$route as $index => $action) {
				$queue->insert($index, $action["priority"]);
			}

			$queue->top();
			while ($queue->valid()) {
				$route = static::$route[$queue->current()];

				if ($route["path"] === $path) {
					$found = static::route($route, $param);
					break;
				}

				/* #\.html$#  */
				if (substr($route["path"], 0, 1) == "#" && substr($route["path"], -1) == "#") {
					if (preg_match($route["path"], $path, $match)) {
						$param[":capture"] = array_slice($match, 1);
						$found = static::route($route, $param);
						break;
					}
				}

				/* /example/* */
				if (strpos($route["path"], "*") !== false) {
					$pattern = "#^" . str_replace("\\*", "(.*)", preg_quote($route["path"], "#")) . "#";
					if (preg_match($pattern, $path, $match)) {
						$param[":arg"] = array_slice($match, 1);
						$found = static::route($route, $param);
						break;
					}
				}

				/* /example/:id */
				if (strpos($route["path"], ":") !== false) {
					$part_p = explode("/", $route["path"]);
					array_shift($part_p);

					$uri = Request::getUriList();
					if (count($uri) == count($part_p)) {
						$matched = true;
						foreach ($part_p as $index => $part) {
							if (":" === substr($part, 0, 1)) {
								$param[substr($part, 1)] = $uri[$index];
								continue;
							}
							if (Request::getUri($index) != $part_p[$index]) {
								$matched = false;
								break;
							}
						}
						if ($matched) {
							$found = static::route($route, $param);
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
	 * Выполняет указанный контроллер
	 *
	 * @param $route
	 * @param $param
	 *
	 * @return mixed|false
	 */
	protected static function route($route, $param) {
		if (is_callable($route["callback"])) {
			return call_user_func($route["callback"], $param);
		}

		return false;
	}

	/**
	 * Перенаправляет на адрес
	 *
	 * @param  string $path
	 * @param  string $app
	 *
	 * @return void
	 * todo:: переписать
	 */
	public static function reroute($path, $app = "") {
		if (strpos($path, "://") === false) {
			if (substr($path, 0, 1) != "/") {
				$path = "/" . $path;
			}
			$path = static::routeUrl($path, $app);
		}

		header("Location: " . $path);
		App::terminate();
	}

	/**
	 * Возвращает ссылку
	 *
	 * @param  string $path
	 * @param  string $app
	 *
	 * @return string
	 */
	public static function routeUrl($path, $app = "") {
		return Request::getUrl($app) . "/" . ltrim($path, "/");
	}
}