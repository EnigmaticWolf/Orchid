<?php

namespace Orchid\Extension;

use DirectoryIterator;
use Orchid\App;

class FileSystem {
	/**
	 * Возвращает листинг директории
	 *
	 * @param string $path
	 *
	 * @return array
	 */
	public static function ls($path = null) {
		$list = [];
		if (($path = App::getPath(($path))) != false) {
			foreach (new DirectoryIterator($path) as $iterator) {
				if ($iterator->isDot()) {
					continue;
				}
				$list[] = $iterator->getBasename();
			}
		}

		return $list;
	}

	/**
	 * Создаёт новую папку
	 *
	 * @param string $folder
	 * @param int    $mode
	 *
	 * @return bool
	 */
	public static function mkdir($folder, $mode = 0755) {
		if (!App::getPath($folder)) {
			return mkdir(static::notExistEntry($folder), $mode);
		}

		return false;
	}

	/**
	 * Рекурсивно удаляет папку и все файлы
	 *
	 * @param string $folder
	 *
	 * @return bool
	 */
	public static function rmdir($folder) {
		if ($folder = App::getPath($folder)) {
			if ($ls = static::ls($folder)) {
				foreach ($ls as $key => $val) {
					$path = $folder . DIRECTORY_SEPARATOR . $val;

					if (is_dir($path) || is_link($path)) {
						static::rmdir($path);
					} elseif (is_file($path)) {
						static::delete($path);
					}
				}
			}

			return rmdir($folder);
		}

		return false;
	}

	/**
	 * Возвращает содержимое файла
	 *
	 * @param string $file
	 *
	 * @return string|bool
	 */
	public static function read($file) {
		if ($file = App::getPath($file)) {
			return file_get_contents($file);
		}

		return false;
	}

	/**
	 * Записывает данные в файл
	 *
	 * @param string $file
	 * @param mixed  $data
	 *
	 * @return bool|int
	 */
	public static function write($file, $data) {
		if (
			($path = App::getPath($file)) ||
			($path = static::notExistEntry($file))
		) {
			return file_put_contents($path, $data);
		}

		return false;
	}

	/**
	 * Копирует файл в указанное место
	 *
	 * @param string $source
	 * @param string $destination
	 *
	 * @return bool
	 */
	public static function copy($source, $destination) {
		if (
			($source = App::getPath($source)) &&
			(
				($new = App::getPath($destination)) ||
				($new = static::notExistEntry($destination))
			)
		) {
			return copy($source, $new);
		}

		return false;
	}

	/**
	 * Переименовывает файл
	 *
	 * @param string $oldName
	 * @param string $newName
	 *
	 * @return bool
	 */
	public static function rename($oldName, $newName) {
		if (
			($old = App::getPath($oldName)) &&
			(
				($new = App::getPath($newName)) ||
				($new = static::notExistEntry($newName))
			)
		) {
			return rename($old, $new);
		}

		return false;
	}

	/**
	 * Удаляет файл
	 *
	 * @param string $file
	 *
	 * @return bool
	 */
	public static function delete($file) {
		if ($file = App::getPath($file)) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Функция помощник для работы с несуществующими файлом или папкой
	 *
	 * @param string $path
	 *
	 * @return bool|string
	 */
	protected static function notExistEntry($path) {
		if (App::isAbsolutePath($path)) {
			return $path;
		}
		if (($file = explode(":", $path)) && count($file) == 2) {
			return App::getPath($file[0] . ":") . $file[1];
		}

		return false;
	}
}