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
use Orchid\Entity\Extension;

class Asset extends Extension {
	protected $include  = [];
	protected $template = '<script id="tpl{name}" type="text/template">{template}</script>';
	protected $tpl      = [];

	/**
	 * Генерирует подключения Asset для js, css, less
	 * @param string $path прямой или ссылочный путь до файла карты
	 * @return null|string
	 */
	public function render($path = "app:config.php") {
		if (($file = $this->app->path($path)) !== false) {
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
					$address = $this->app->pathToUrl($file);
				}

				switch (pathinfo($file)["extension"]) {
					case "js": {
						$this->include[] = '<script type="text/javascript" src="' . $address . '"></script>';
						break;
					}
					case "css": {
						$this->include[] = '<link rel="stylesheet" type="text/css" href="' . $address . '" />';
						break;
					}
					case "less": {
						$this->include[] = '<link rel="stylesheet/less" type="text/css" href="' . $address . '" />';
						break;
					}
				}
			}
		}

		return $this->include ? "\n" . implode("\n", $this->include) . "\n" : null;
	}

	/**
	 * Собирает все шаблоны из папки приложения и из всех подключенных модулей
	 * @return string
	 */
	public function template() {
		// папка приложения
		if ($path = $this->app->path("template:")) {
			$this->templateIterator($path);
		}

		// папки модулей
		$modules = array_keys($this->app->retrieve("module", []));
		foreach ($modules as $module) {
			if ($path = $this->app->path($module . ":template")) {
				$this->templateIterator($path);
			}
		}

		return $this->tpl ? "\n" . implode("\n", $this->tpl) . "\n" : null;
	}

	/**
	 * Рекурсивно обходит все папки и собирает шаблоны
	 * @param $path
	 * @return string
	 */
	protected function templateIterator($path) {
		foreach (new DirectoryIterator($path) as $item) {
			if (!$item->isDot()) {
				if ($item->isDir()) {
					$this->templateIterator($this->app->path($path . $item->getBasename()));
				} elseif ($item->isFile()) {
					$file = str_replace("//", "/", $path . "/" . $item->getBasename());
					$this->tpl[] = str_replace(
						["{name}", "{template}"],
						[str_replace(["/", ".tpl"], ["-", ""], explode("template", $file)[1]), "\n\r" . file_get_contents($file) . "\n\r"],
						$this->template
					);
				}
			}
		}
	}
}
