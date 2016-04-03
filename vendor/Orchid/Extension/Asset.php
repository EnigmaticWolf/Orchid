<?php

namespace Orchid\Extension;

use DirectoryIterator;
use Orchid\App;
use Orchid\Registry;
use Orchid\Request;

class Asset {
	/**
	 * Карта ресурсов
	 * @var array
	 */
	public static $map = [];

	/**
	 * Генерирует и возвращает строку подключений ресурсов на основе карты ресурсов и параметров
	 * @return string|null
	 */
	public static function render() {
		$include = [];

		if (static::$map) {
			// общие ресурсы
			if (isset(static::$map["*"])) {
				$include = array_merge($include, static::renderIterator(static::$map["*"]));
			}

			// ресурсы для определённого контроллера
			if (($controller = reset(Registry::get("uri", []))) !== false && isset(static::$map[$controller . "/*"])) {
				$include = array_merge($include, static::renderIterator(static::$map[$controller . "/*"]));
			}

			// ресурсы для определённого адреса
			if (($path = substr(Request::getPath(), 1)) && isset(static::$map[$path])) {
				$include = array_merge($include, static::renderIterator(static::$map[$path]));
			}

			// если предыдущие проверки не дали результатов
			if (empty($include)) {
				$include = array_merge($include, static::renderIterator(static::$map));
			}
		}

		return $include ? implode("\n", $include) : null;
	}

	/**
	 * Обходит переданный массив и возвращает массив строк для подключения ресурсов
	 * @param array $list
	 * @return array
	 */
	protected static function renderIterator(array $list) {
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
	 * Собирает все шаблоны из папок template: и из всех подключенных модулей
	 * @return string
	 */
	public static function template() {
		$template = [];

		// объявленные вручную каталоги с шаблонами
		foreach (Registry::get("path", ["template" => []])["template"] as $path) {
			$template = array_merge($template, static::templateIterator($path));
		}

		// модули в которых есть шаблоны
		foreach (Registry::get("module") as $module) {
			if ($path = App::path($module . ":template")) {
				$template = array_merge($template, static::templateIterator($path));
			}
		}

		return $template ? implode("\n", $template) : null;
	}

	/**
	 * Рекурсивно обходит указанную директорию и собирает шаблоны
	 * @param string $dir
	 * @param string $initial
	 * @return string
	 */
	protected static function templateIterator($dir, $initial = "") {
		$dir = realpath($dir);
		$template = [];

		foreach (new DirectoryIterator($dir) as $item) {
			if (!$item->isDot()) {
				if ($item->isDir()) {
					$template = array_merge($template, static::templateIterator(App::path($dir . "/" . $item->getBasename()), $dir));
				} else {
					if ($item->isFile()) {
						$file = realpath($item->getPathname());
						$ext = pathinfo($file)["extension"];
						if (in_array($ext, ["tpl", "ejs"])) {
							$name = str_replace(["/", ".tpl", ".ejs"], ["-", "", ""], explode($initial ? $initial : $dir, $file)[1]);

							switch ($ext) {
								case "tpl": {
									$template[] = '<script id="tpl' . $name . '" type="text/template">' . App::render($file) . '</script>';
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
