<?php
/*
 * Copyright (c) 2011-2014 AEngine
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

namespace Orchid\Extension;

use DirectoryIterator;
use Orchid\App;
use Orchid\Entity\Extension;

class Asset extends Extension {
	protected static $include  = [];
	protected static $template = '<script id="tpl{name}" type="text/template">{template}</script>';
	protected static $tpl      = [];

	/**
	 * Генерирует подключения Asset для js, css, less
	 * @param string $path прямой или ссылочный путь до файла карты
	 * @return null|string
	 */
	public static function render($path = "app:config.php") {
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
		$modules = array_keys(App::retrieve("module", []));
		foreach ($modules as $module) {
			if ($path = App::path($module . ":template")) {
				static::templateIterator($path);
			}
		}

		return static::$tpl ? "\n" . implode("\n", static::$tpl) . "\n" : null;
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
					static::templateIterator(App::path($path . $item->getBasename()));
				} elseif ($item->isFile()) {
					$file = str_replace("//", "/", $path . "/" . $item->getBasename());
					static::$tpl[] = str_replace(
						["{name}", "{template}"],
						[str_replace(["/", ".tpl"], ["-", ""], explode("template", $file)[1]), "\n\r" . file_get_contents($file) . "\n\r"],
						static::$template
					);
				}
			}
		}
	}
}
