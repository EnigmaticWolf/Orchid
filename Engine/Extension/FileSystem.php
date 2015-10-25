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

namespace Engine\Extension;

use DirectoryIterator;
use Engine\Entity\Extension;

class FileSystem extends Extension {
	/**
	 * Листинг директории
	 * @param string|null $dirName абсолютный или ссылочный путь
	 * @return array
	 */
	public function ls($dirName = null) {
		$list = [];
		if (($dirName = $this->app->path(($dirName ? $dirName : $this->app["base_dir"]))) != false) {
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
	 * @param int $mode уровень доступа для владельца файла
	 * @return bool
	 */
	public function mkdir($dirName, $mode = 0755) {
		if (!$this->app->path($dirName)) {
			return mkdir($this->notExistEntry($dirName), $mode);
		}

		return false;
	}

	/**
	 * Удаляет папку и все файлы, рекурсивно
	 * @param string $dirName абсолютный или ссылочный путь
	 * @return bool
	 */
	public function rmdir($dirName) {
		if ($dirName = $this->app->path($dirName)) {
			if ($ls = $this->ls($dirName)) {
				foreach ($ls as $key => $val) {
					$path = $dirName . DIRECTORY_SEPARATOR . $val;

					if (is_dir($path) || is_link($path)) {
						$this->rmdir($path);
					} elseif (is_file($path)) {
						$this->delete($path);
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
	public function read($file) {
		if ($file = $this->app->path($file)) {
			return file_get_contents($file);
		}

		return false;
	}

	/**
	 * Записать данные в файл
	 * @param string $file абсолютный или ссылочный путь
	 * @param mixed $data содержимое для записи в файл
	 * @return bool|int
	 */
	public function write($file, $data) {
		if (
			($path = $this->app->path($file)) ||
			($path = $this->notExistEntry($file))
		) {
			return file_put_contents($path, $data);
		}

		return false;
	}

	/**
	 * Скопировать файл в указанное место
	 * @param string $source абсолютный или ссылочный путь
	 * @param string $destination абсолютный или ссылочный путь
	 * @return bool
	 */
	public function copy($source, $destination) {
		if (
			($source = $this->app->path($source)) &&
			(
				($new = $this->app->path($destination)) ||
				($new = $this->notExistEntry($destination))
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
	public function rename($oldName, $newName) {
		if (
			($old = $this->app->path($oldName)) &&
			(
				($new = $this->app->path($newName)) ||
				($new = $this->notExistEntry($newName))
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
	public function delete($file) {
		if ($file = $this->app->path($file)) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Функция помощник для работы с несуществующими файлом или папкой
	 * @param string $path абсолютный или ссылочный путь
	 * @return bool|string
	 */
	protected function notExistEntry($path) {
		if ($this->app->isAbsolutePath($path)) {
			return $path;
		}
		if (($file = explode(":", $path)) && count($file) == 2) {
			return $this->app->path($file[0] . ":") . $file[1];
		}

		return false;
	}
}