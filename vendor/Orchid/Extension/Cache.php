<?php

namespace Orchid\Extension;

use Orchid\App;
use RecursiveDirectoryIterator;

class Cache {
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

		return file_put_contents(static::getCacheFilePath($key), serialize($data));
	}

	/**
	 * Читаемт данные из временного файла
	 * @param string $key     ключ
	 * @param mixed  $default возвращаемое значение, если данных нет
	 * @return mixed
	 */
	public static function read($key, $default = null) {
		$file = static::getCacheFilePath($key);
		$data = file_exists($file) ? file_get_contents($file) : false;

		if ($data !== false) {
			$data = unserialize($data);

			if (($data["expire"] > time()) || $data["expire"] < 0) {
				return unserialize($data["value"]);
			}

			static::delete(static::getCacheFilePath($key));
		}

		return $default;
	}

	/**
	 * Удаляет временный файл
	 * @param string $key ключ
	 * @return boolean
	 */
	public static function delete($key) {
		$file = static::getCacheFilePath($key);

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
		$path = App::getBaseDir() . "/storage/cache";

		if (($cache = App::path("cache:")) !== false) {
			$path = $cache;
		}

		$iterator = new RecursiveDirectoryIterator($path . "/");

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

	/**
	 * Возвращает путь и название временного файла
	 * @param string $key
	 * @return string
	 */
	protected static function getCacheFilePath($key) {
		// директориия хранилища по-умолчанию
		$path = App::getBaseDir() . "/storage/cache/";

		if (($cache = App::path("cache:")) !== false) {
			$path = $cache;
		}

		return $path . md5(App::getSecret() . ":" . $key) . ".cache";
	}
}
