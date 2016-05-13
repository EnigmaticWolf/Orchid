<?php

namespace Orchid;

use Closure;
use RuntimeException;

class Router {
	/**
	 * Хранилище объявленных роутов
	 *
	 * @var array
	 */
	protected static $route = [];

	/**
	 * Метод-ссылка для метода bind объявляет Get роутинг
	 *
	 * @param      $path
	 * @param      $callback
	 * @param bool $condition
	 * @param int  $priority
	 */
	public static function get($path, $callback, $condition = true, $priority = 0) {
		static::bind($path, $callback, Request::METHOD_GET, $condition, $priority);
	}

	/**
	 * Метод-ссылка для метода bind объявляет Post роутинг
	 *
	 * @param      $path
	 * @param      $callback
	 * @param bool $condition
	 * @param int  $priority
	 */
	public static function post($path, $callback, $condition = true, $priority = 0) {
		static::bind($path, $callback, Request::METHOD_POST, $condition, $priority);
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
		if ((is_null($method) || Request::isMethod($method)) && $condition) {
			static::$route[] = [
				"path"     => $path,
				"callback" => $callback,
				"priority" => $priority,
			];
		}
	}

	/**
	 * Метод для перебора объявленных роутингов
	 *
	 * @return mixed|false
	 */
	public static function dispatch() {
		$found = false;

		if (static::$route) {
			$path = Request::getPath();

			arsort(static::$route, SORT_NUMERIC);
			foreach (static::$route as $route) {
				$param = [];

				if ($route["path"] === $path) {
					$found = static::route($route["callback"], $param);
					break;
				}

				/* #\.html$#  */
				if (substr($route["path"], 0, 1) == "#" && substr($route["path"], -1) == "#") {
					if (preg_match($route["path"], $path, $match)) {
						$param[":capture"] = array_slice($match, 1);
						$found = static::route($route["callback"], $param);
						break;
					}
				}

				/* /example/* */
				if (strpos($route["path"], "*") !== false) {
					$pattern = "#^" . str_replace("\\*", "(.*)", preg_quote($route["path"], "#")) . "#";
					if (preg_match($pattern, $path, $match)) {
						$param[":arg"] = array_slice($match, 1);
						$found = static::route($route["callback"], $param);
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
							if ($uri[$index] != $part_p[$index]) {
								$matched = false;
								break;
							}
						}
						if ($matched) {
							$found = static::route($route["callback"], $param);
							break;
						}
					}
				}
			}
		}

		return $found;
	}

	/**
	 * Выполняет указанный контроллер
	 *
	 * @param Closure $callable
	 * @param array   $param
	 *
	 * @return mixed
	 * @throws RuntimeException
	 */
	protected static function route($callable, $param = []) {
		if (is_callable($callable)) {
			return call_user_func_array($callable, $param);
		}

		throw new RuntimeException("Не удалось выполнить функцию");
	}
}