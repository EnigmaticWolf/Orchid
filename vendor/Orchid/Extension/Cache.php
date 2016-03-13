<?php

namespace Orchid\Extension;

use Closure;
use Orchid\App;
use RecursiveDirectoryIterator;

class Cache {
	/**
	 * Возвращает путь и название файла
	 * @param string $key
	 * @return string
	 */
	protected static function getFilePath($key) {
		return App::retrieve("path/cache/0", App::get("base_dir") . "/storage/cache") . "/" . md5(App::get("secret") . ":" . $key) . ".cache";
	}

	/**
	 * Записывает данные во временный файл
	 * @param string $key      ключ
	 * @param mixed  $value    значение для записи
	 * @param int    $duration время жизни файла (По умолчанию -1 - вечно)
	 * @return int|false количество байт записанных в случае успеха
	 */
	public static function write($key, $value, $duration = -1) {
		$data = [
			"expire" => ($duration >= 0) ? (is_string($duration) ? strtotime($duration) : time() + $duration) : $duration,
			"value"  => serialize($value),
		];

		return file_put_contents(static::getFilePath($key), serialize($data));
	}

	/**
	 * Читаемт данные из временного файла
	 * @param string $key     ключ
	 * @param mixed  $default возвращаемое значение, если данных нет
	 * @return mixed
	 */
	public static function read($key, $default = null) {
		$file = static::getFilePath($key);
		$data = file_exists($file) ? file_get_contents($file) : false;

		if ($data !== false) {
			$data = unserialize($data);

			if (($data["expire"] > time()) || $data["expire"] < 0) {
				return unserialize($data["value"]);
			}

			static::delete(static::getFilePath($key));
		}

		return $default;
	}

	/**
	 * Удаляет временный файл
	 * @param string $key ключ
	 * @return boolean
	 */
	public static function delete($key) {
		$file = static::getFilePath($key);

		if (file_exists($file)) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Удаляет все временные файлы
	 * @return boolean
	 */
	public static function flush() {
		$iterator = new RecursiveDirectoryIterator(App::retrieve("path/cache/0", App::get("base_dir") . "/storage/cache") . "/");

		/** @var RecursiveDirectoryIterator $item */
		foreach ($iterator as $item) {
			if ($item->isFile()) {
				$file = realpath($item->getPathname());

				if (pathinfo($file)["extension"] == "cache") {
					unlink($file);
				}
			}
		}

		return true;
	}
}
