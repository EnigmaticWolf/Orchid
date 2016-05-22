<?php

namespace Orchid;

use Closure;
use RuntimeException;
use Orchid\Entity\Exception\NoSuchMethodException;

class Router {
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * @var Request
	 */
	protected $request;

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
	public function __construct(App $app) {
		$this->app = $app;
	}

	/**
	 * Bind GET request to route
	 *
	 * @param string         $pattern
	 * @param Closure|string $callable
	 * @param int            $priority
	 *
	 * @return $this
	 */
	public function get($pattern, $callable, $priority = 0) {
		return $this->bind($pattern, $callable, Request::METHOD_GET, $priority);
	}

	/**
	 * Bind POST request to route
	 *
	 * @param string         $pattern
	 * @param Closure|string $callable
	 * @param int            $priority
	 *
	 * @return $this
	 */
	public function post($pattern, $callable, $priority = 0) {
		return $this->bind($pattern, $callable, Request::METHOD_POST, $priority);
	}

	/**
	 * Bind request to route
	 *
	 * @param string         $pattern
	 * @param Closure|string $callable
	 * @param string         $method
	 * @param int            $priority
	 *
	 * @return $this
	 */
	public function bind($pattern, $callable, $method = Request::METHOD_GET, $priority = 0) {
		$this->routes[] = [
			"method"   => strtoupper($method),
			"pattern"  => $pattern,
			"callable" => $callable,
			"priority" => $priority,
		];

		return $this;
	}

	/**
	 * Dispatch route
	 *
	 * Runs the appropriate action
	 * It will execute the before() method before the action
	 * and after() method after the action finishes
	 *
	 * @return mixed
	 * @throws RuntimeException
	 * @throws NoSuchMethodException
	 */
	public function dispatch() {
		$method = $this->app->request()->getMethod();
		$pathname = $this->app->request()->getPathname();
		$uri = $this->app->request()->getUri();

		$found = null;
		$params = [];

		if ($this->routes) {
			usort($this->routes, [$this, "compare"]);

			foreach ($this->routes as $route) {
				if ($route["method"] == $method) {
					if ($route["pattern"] === $pathname) {
						$found = $route["callable"];
						break;
					}

					/* #\.html$#  */
					if (substr($route["pattern"], 0, 1) == "#" && substr($route["pattern"], -1) == "#") {
						if (preg_match($route["pattern"], $pathname, $match)) {
							$params[":capture"] = array_slice($match, 1);
							$found = $route["callable"];
							break;
						}
					}

					/* /example/* */
					if (strpos($route["pattern"], "*") !== false) {
						$pattern = "#^" . str_replace("\\*", "(.*)", preg_quote($route["pattern"], "#")) . "#";
						if (preg_match($pattern, $pathname, $match)) {
							$params[":arg"] = array_slice($match, 1);
							$found = $route["callable"];
							break;
						}
					}

					/* /example/:id */
					if (strpos($route["pattern"], ":") !== false) {
						$parts = explode("/", $route["pattern"]);
						array_shift($parts);

						if (count($uri) == count($parts)) {
							$matched = true;
							foreach ($parts as $index => $part) {
								if (":" === substr($part, 0, 1)) {
									$params[substr($part, 1)] = $uri[$index];
									continue;
								}
								if ($uri[$index] != $parts[$index]) {
									$matched = false;
									break;
								}
							}
							if ($matched) {
								$found = $route["callable"];
								break;
							}
						}
					}
				}
			}
		} else {
			throw new RuntimeException("Route list is empty");
		}

		if ($found) {
			if (is_object($found)) {
				return call_user_func($found, $this->app, $params);
			}
			if (is_string($found)) {
				$controller = new $found($this->app);
				$action = $this->request->getUri(0, "index");
				$result = null;

				if (method_exists($controller, "before")) {
					call_user_func([$controller, "before"], $action);
				}

				if ($controller->execute) {
					if (method_exists($controller, $action)) {
						$result = call_user_func([$controller, $action], $params);
					} else {
						throw new NoSuchMethodException("Method '" . $action . "' doesn't exist in " . get_class());
					}
				}

				if ($controller->execute) {
					if (method_exists($controller, "after")) {
						call_user_func([$controller, "after"], $action);
					}
				}

				return $result;
			}
		}

		throw new RuntimeException("Failed to find and execute the function");
	}

	/**
	 * @param $a
	 * @param $b
	 *
	 * @return mixed
	 */
	protected function compare($a, $b) {
		return $b["priority"] - $a["priority"];
	}
}