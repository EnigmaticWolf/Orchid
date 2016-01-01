<?php

/*
 * Copyright (c) 2011-2016 AEngine
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

class FileSystem extends Extension {
	/**
	 * Листинг директории
	 * @param string|null $dirName абсолютный или ссылочный путь
	 * @return array
	 */
	public static function ls($dirName = null) {
		$list = [];
		if (($dirName = App::path(($dirName ? $dirName : App::get("base_dir")))) != false) {
			foreach (new DirectoryIterator($dirName) as $iterator) {
				if ($iterator->isDot()) {
					continue;
				}
				$list[] = $iterator->getBasename();
			}
		}

		return $list;
	}

	/**
	 * Создать новую папку
	 * @param string $dirName абсолютный или ссылочный путь
	 * @param int    $mode    уровень доступа для владельца файла
	 * @return bool
	 */
	public static function mkdir($dirName, $mode = 0755) {
		if (!App::path($dirName)) {
			return mkdir(static::notExistEntry($dirName), $mode);
		}

		return false;
	}

	/**
	 * Удаляет папку и все файлы, рекурсивно
	 * @param string $dirName абсолютный или ссылочный путь
	 * @return bool
	 */
	public static function rmdir($dirName) {
		if ($dirName = App::path($dirName)) {
			if ($ls = static::ls($dirName)) {
				foreach ($ls as $key => $val) {
					$path = $dirName . DIRECTORY_SEPARATOR . $val;

					if (is_dir($path) || is_link($path)) {
						static::rmdir($path);
					} elseif (is_file($path)) {
						static::delete($path);
					}
				}
			}

			return rmdir($dirName);
		}

		return false;
	}

	/**
	 * Прочитать файл
	 * @param string $file абсолютный или ссылочный путь
	 * @return bool|string
	 */
	public static function read($file) {
		if ($file = App::path($file)) {
			return file_get_contents($file);
		}

		return false;
	}

	/**
	 * Записать данные в файл
	 * @param string $file абсолютный или ссылочный путь
	 * @param mixed  $data содержимое для записи в файл
	 * @return bool|int
	 */
	public static function write($file, $data) {
		if (
			($path = App::path($file)) ||
			($path = static::notExistEntry($file))
		) {
			return file_put_contents($path, $data);
		}

		return false;
	}

	/**
	 * Скопировать файл в указанное место
	 * @param string $source      абсолютный или ссылочный путь
	 * @param string $destination абсолютный или ссылочный путь
	 * @return bool
	 */
	public static function copy($source, $destination) {
		if (
			($source = App::path($source)) &&
			(
				($new = App::path($destination)) ||
				($new = static::notExistEntry($destination))
			)
		) {
			return copy($source, $new);
		}

		return false;
	}

	/**
	 * Переименовать файл
	 * @param string $oldName абсолютный или ссылочный путь
	 * @param string $newName абсолютный или ссылочный путь
	 * @return bool
	 */
	public static function rename($oldName, $newName) {
		if (
			($old = App::path($oldName)) &&
			(
				($new = App::path($newName)) ||
				($new = static::notExistEntry($newName))
			)
		) {
			return rename($old, $new);
		}

		return false;
	}

	/**
	 * Удалить файл
	 * @param string $file абсолютный или ссылочный путь
	 * @return bool
	 */
	public static function delete($file) {
		if ($file = App::path($file)) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Функция помощник для работы с несуществующими файлом или папкой
	 * @param string $path абсолютный или ссылочный путь
	 * @return bool|string
	 */
	protected static function notExistEntry($path) {
		if (App::isAbsolutePath($path)) {
			return $path;
		}
		if (($file = explode(":", $path)) && count($file) == 2) {
			return App::path($file[0] . ":") . $file[1];
		}

		return false;
	}
}