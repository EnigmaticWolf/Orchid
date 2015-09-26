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

use Engine\Extension;
use RecursiveDirectoryIterator;

class Cache extends Extension {
	public    $prefix    = null;
	protected $cachePath = null;

	public function initialize() {
		$this->cachePath = ($this->app->path("cache:") ? rtrim($this->app->path("cache:")) . "/" : "/");
		$this->prefix    = (isset($this->app["app"]) ? $this->app["app"] : "cache");
	}

	/**
	 * Метод сериализует и записывает данные в файл
	 * @param String $key      ключевое слово
	 * @param String $value    значение для записи
	 * @param Int    $duration время жизни файла (По умолчанию -1 - вечно)
	 * @return Int|False количество байт записанных в случае успеха
	 */
	public function write($key, $value, $duration = -1) {
		$expire = ($duration == -1) ? -1 : (time() + (is_string($duration) ? strtotime($duration) : $duration));
		$data   = [
			"expire" => $expire,
			"value"  => serialize($value),
		];

		return file_put_contents($this->cachePath . md5($this->prefix . ":" . $key) . ".cache", serialize($data));
	}

	/**
	 * Метод возвращает десериализованные данные из файла
	 * @param String $key     ключевое слово
	 * @param Mixed  $default значение возвращаемое если данных нет
	 * @return Mixed
	 */
	public function read($key, $default = null) {
		$data = @file_get_contents($this->cachePath . md5($this->prefix . ":" . $key) . ".cache");

		if ($data === "") {
			return $default;
		} else {
			$time = time();
			$data = unserialize($data);

			if (($data["expire"] < $time) && $data["expire"] != -1) {
				$this->delete($key);

				return is_callable($default) ? call_user_func($default) : $default;
			}

			return unserialize($data["value"]);
		}
	}

	/**
	 * Метод удаляет файл кеша с заданным ключем
	 * @param String $key ключевое слово
	 * @return Boolean
	 */
	public function delete($key) {
		$file = $this->cachePath . md5($this->prefix . ":" . $key) . ".cache";

		if (file_exists($file)) {
			return @unlink($file);
		}

		return false;
	}

	/**
	 * Метод удаляет все файлы кеша
	 */
	public function clear() {
		$iterator = new RecursiveDirectoryIterator($this->cachePath);

		/** @var RecursiveDirectoryIterator $file */
		foreach ($iterator as $file) {
			if ($file->isFile() && substr($file, -6) == ".cache") {
				@unlink($this->cachePath . $file->getFilename());
			}
		}
	}
}
