<?php

namespace Orchid\Extension;

use DirectoryIterator;
use Orchid\App;
use Orchid\Entity\View;

class Asset {
	/**
	 * Resource map
	 *
	 * @var array
	 */
	public static $map = [];

	/**
	 * Generates resource string based on the resources map
	 *
	 * @return null|string
	 */
	public static function resource() {
		$request = App::getInstance()->request();
		$include = [];

		if (static::$map) {
			// shared resources
			if (isset(static::$map["*"])) {
				$include = array_merge($include, static::resourceIterator(static::$map["*"]));
			}

			// resources for a particular controller
			if (($controller = $request->getUri(0, false)) && isset(static::$map[$controller . "/*"])) {
				$include = array_merge($include, static::resourceIterator(static::$map[$controller . "/*"]));
			}

			// resources for a particular address
			if (($path = substr($request->getPathname(), 1)) && isset(static::$map[$path])) {
				$include = array_merge($include, static::resourceIterator(static::$map[$path]));
			}

			// previous checks have failed
			if (empty($include)) {
				$include = array_merge($include, static::resourceIterator(static::$map));
			}
		}

		return $include ? implode("\n", $include) : null;
	}

	/**
	 * Bypasses the passed array and returns an array of strings to connect resources
	 *
	 * @param array $list
	 *
	 * @return array
	 */
	protected static function resourceIterator(array $list) {
		$include = [];

		foreach ($list as $address) {
			switch (pathinfo($address)["extension"]) {
				case "js": {
					$include[] = '<script type="text/javascript" src="' . $address . '"></script>';
					break;
				}
				case "css": {
					$include[] = '<link rel="stylesheet" type="text/css" href="' . $address . '" />';
					break;
				}
				case "less": {
					$include[] = '<link rel="stylesheet/less" type="text/css" href="' . $address . '" />';
					break;
				}
			}
		}

		return $include;
	}

	/**
	 * Collect all of the templates from folder "template" and of all loaded modules
	 *
	 * @return string
	 */
	public static function template() {
		$app = App::getInstance();

		$template = [];

		// catalog manually from the templates
		foreach ($app->pathList("template") as $path) {
			$template = array_merge($template, static::templateIterator($app, $path));
		}

		// modules that have templates
		foreach ($app->getModules() as $module) {
			if ($path = $app->path($module . ":template")) {
				$template = array_merge($template, static::templateIterator($app, $path));
			}
		}

		return $template ? implode("\n", $template) : null;
	}

	/**
	 * Recursively specified directory and collects templates
	 *
	 * @param App    $app
	 * @param string $dir
	 * @param string $initial
	 *
	 * @return string
	 * @throws \Orchid\Entity\Exception\FileNotFoundException
	 */
	protected static function templateIterator(App $app, $dir, $initial = "") {
		$dir = realpath($dir);
		$template = [];

		foreach (new DirectoryIterator($dir) as $item) {
			if (!$item->isDot()) {
				if ($item->isDir()) {
					$template = array_merge($template, static::templateIterator($app, $app->path($dir . "/" . $item->getBasename()), $dir));
				} else {
					if ($item->isFile()) {
						$file = realpath($item->getPathname());
						$ext = pathinfo($file)["extension"];
						if (in_array($ext, ["tpl", "ejs"])) {
							$name = str_replace(["/", ".tpl", ".ejs"], ["-", "", ""], explode($initial ? $initial : $dir, $file)[1]);

							switch ($ext) {
								case "tpl": {
									$template[] = '<script id="tpl' . $name . '" type="text/template">' . View::fetch($file) . '</script>';
									break;
								}
								case "ejs": {
									$template[] = '<script id="tpl' . $name . '" type="text/template">' . file_get_contents($file) . '</script>';
									break;
								}
							}
						}
					}
				}
			}
		}

		return $template;
	}
}
