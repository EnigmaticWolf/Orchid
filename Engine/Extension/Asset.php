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

namespace Engine\Extension;

use DirectoryIterator;
use Engine\Entity\Extension;

class Asset extends Extension {
	/**
	 * Компилирует стили в один файл (для debug каждый файл отдельно) и возвращает link тэг
	 * @param string|array $assets
	 * @param string       $path
	 * @param int          $cache
	 * @param string       $name
	 * @param bool|false   $version
	 * @return null|string
	 */
	public function style($assets, $path, $cache = 0, $name = "style.css", $version = false) {
		$assets = array_unique((array)$assets);

		if ($this->app->retrieve("debug", false)) {
			$tag = [];

			foreach ($assets as $file) {
				if ($path = $this->app->path($file)) {
					$tag[] = '<link rel="stylesheet" type="text/css" href="' . $this->app->pathToUrl($file) . '" />';
				}
			}

			return implode("", $tag) . "\n\r";
		} else {
			if ($path = $this->app->path($path)) {
				$href = rtrim($this->app->pathToUrl($path), '/') . "/{$name}" . ($version ? "?ver={$version}" : "");
				$path .= "/{$name}";
				$tag = '<link rel="stylesheet" type="text/css" href="' . $href . '" />' . "\n\r";

				if ($cache && file_exists($path) && (time() - filemtime($path)) < $cache) {
					return $tag;
				}

				$result = $this->compile($assets);
				if ($result) {
					file_put_contents($path, $result);

					return $tag . "\n\r";
				}
			}
		}

		return null;
	}

	/**
	 * Компилирует скрипты в один файл (для debug каждый файл отдельно) и возвращает script тэг
	 * @param string|array $assets
	 * @param string       $path
	 * @param int          $cache
	 * @param string       $name
	 * @param bool|false   $version
	 * @return null|string
	 */
	public function script($assets, $path, $cache = 0, $name = "script.js", $version = false) {
		$assets = array_unique((array)$assets);

		if ($this->app->retrieve("debug", false)) {
			$tag = [];

			foreach ($assets as $file) {
				if ($path = $this->app->pathToUrl($file)) {
					$tag[] = '<script type="text/javascript" src="' . $path . '"></script>';
				}
			}

			return implode("", $tag) . "\n\r";
		} else {
			if ($path = $this->app->path($path)) {
				$href = rtrim($this->app->pathToUrl($path), '/') . "/{$name}" . ($version ? "?ver={$version}" : "");
				$tag  = '<script type="text/javascript" src="' . $href . '"></script>';
				$path .= "/{$name}";

				if ($cache && file_exists($path) && (time() - filemtime($path)) < $cache) {
					return $tag;
				}

				$result = $this->compile($assets);
				if ($result) {
					file_put_contents($path, $result);

					return $tag . "\n\r";
				}
			}
		}

		return null;
	}

	/**
	 * Собирает указанные файлы в один файл
	 * @param array $assets
	 * @return string
	 */
	protected function compile($assets) {
		$content = "";
		foreach ($assets as $file) {
			if ($path = $this->app->path($file)) {
				$content .= file_get_contents($path) . "\n\r";
			}
		}

		return $content;
	}

	/**
	 * Собирает все шаблоны из всех подключенных модулей
	 * @return string
	 */
	public function template() {
		$tpl = "";
		$modules = array_keys($this->app->retrieve("module", []));

		foreach($modules as $module) {
			if ($path = $this->app->path($module . ":template")) {
				$tpl .= $this->templateIterator($path);
			}
		}

		return $tpl;
	}

	/**
	 * Рекурсивоно обходит все папки и собирает шаблоны
	 * @param $path
	 * @return string
	 */
	protected function templateIterator($path) {
		$template = '<script id="tpl{name}" type="text/template">{template}</script>' . "\n\r";
		$tpl = "";

		foreach (new DirectoryIterator($path) as $item) {
			if (!$item->isDot()) {
				if ($item->isDir()) {
					$tpl .= $this->templateIterator($this->app->path($path . $item->getBasename()));
				} elseif ($item->isFile()) {
					$content = file_get_contents($this->app->path($path . "/" . $item->getBasename()));
					$tpl .= str_replace(['{name}', '{template}'], [str_replace(['/', '.tpl'], ['-', ''], explode('template', $path . "/" . $item->getBasename())[1]), "\n\r" . $content . "\n\r"], $template);
				}
			}
		}

		return $tpl;
	}
}
