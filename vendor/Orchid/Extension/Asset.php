<?php

namespace Orchid\Extension;

use DirectoryIterator;
use Orchid\App;
use Orchid\Entity\Extension;
use Orchid\Entity\Module;

class Asset extends Extension {
	protected static $include  = [];
	protected static $template = [];

	/**
	 * Генерирует подключения Asset для js, css, less
	 * @param string $path прямой или ссылочный путь до файла карты
	 * @return null|string
	 */
	public static function render($path = "app:configuration.php") {
		if (($file = App::path($path)) !== false) {
			/*
			 * Подключение карты файлов, пример массива:
			 *	[
			 *		"link:filename.ext" => "internal/address/filename.ext"
			 *	]
			 */
			$config = array_unique((array)require_once($file));

			foreach ($config as $file => $address) {
				if (is_numeric($file)) {
					$file    = $address;
					$address = App::pathToUrl($file);
				}

				switch (pathinfo($file)["extension"]) {
					case "js": {
						static::$include[] = '<script type="text/javascript" src="' . $address . '"></script>';
						break;
					}
					case "css": {
						static::$include[] = '<link rel="stylesheet" type="text/css" href="' . $address . '" />';
						break;
					}
					case "less": {
						static::$include[] = '<link rel="stylesheet/less" type="text/css" href="' . $address . '" />';
						break;
					}
				}
			}
		}

		return static::$include ? "\n" . implode("\n", static::$include) . "\n" : null;
	}

	/**
	 * Собирает все шаблоны из папки приложения и из всех подключенных модулей
	 * @return string
	 */
	public static function template() {
		// папка приложения
		if ($path = App::path("template:")) {
			static::templateIterator($path);
		}

		// папки модулей
		foreach (Module::$list as $module) {
			if ($path = App::path($module . ":template")) {
				static::templateIterator($path);
			}
		}

		return static::$template ? "\n" . implode("\n", static::$template) . "\n" : null;
	}

	/**
	 * Рекурсивно обходит все папки и собирает шаблоны
	 * @param $path
	 * @return string
	 */
	protected static function templateIterator($path) {
		foreach (new DirectoryIterator($path) as $item) {
			if (!$item->isDot()) {
				if ($item->isDir()) {
					static::templateIterator(App::path($path . "/" . $item->getBasename()));
				} else {
					$file = str_replace("//", "/", $path . "/" . $item->getBasename());

					if ($item->isFile() && in_array(pathinfo($file)["extension"], ["tpl", "ejs"])) {
						$name    = str_replace(["/", ".tpl", ".ejs"], ["-", "", ""], explode("template", $file)[1]);
						$content = file_get_contents($file);

						static::$template[] = '<script id="tpl' . $name . '" type="text/template">' . $content . '</script>';
					}
				}
			}
		}
	}
}
