<?php

use Orchid\App;
use Orchid\Entity\Extension;

class Cache extends Extension {
	protected $cachePath = null;

	/**
	 * Сериализовать и записать данные в временный файл
	 * @param string $key      ключ
	 * @param string $value    значение для записи
	 * @param int    $duration время жизни файла (По умолчанию -1 - вечно)
	 * @return int|false количество байт записанных в случае успеха
	 */
	public static function write($key, $value, $duration = -1) {
		$data = [
			"expire" => ($duration != -1) ? (is_string($duration) ? strtotime($duration) : time() + $duration) : $duration,
			"value"  => serialize($value),
		];

		return file_put_contents(App::retrieve("path/cache/0", App::get("base_dir") . "/cache") . "/" . md5(App::get("app") . ":" . $key) . ".cache", serialize($data));
	}

	/**
	 * Десериализовать данные из временного файла
	 * @param string        $key     ключ
	 * @param mixed|Closure $default возвращаемое значение, если данных нет
	 * @return mixed
	 */
	public static function read($key, $default = null) {
		$file = App::retrieve("path/cache/0", App::get("base_dir") . "/cache") . "/" . md5(App::get("app") . ":" . $key) . ".cache";
		$data = file_exists($file) ? file_get_contents($file) : false;

		if ($data !== false) {
			$data = unserialize($data);

			if (($data["expire"] > time()) || $data["expire"] == -1) {
				return unserialize($data["value"]);
			}

			static::delete($key);
		}

		return is_callable($default) ? call_user_func($default, $key) : $default;
	}

	/**
	 * Удалить временный файл
	 * @param string $key ключ
	 * @return boolean
	 */
	public static function delete($key) {
		$file = App::retrieve("path/cache/0", App::get("base_dir") . "/cache") . "/" . md5(App::get("app") . ":" . $key) . ".cache";

		if (file_exists($file)) {
			return unlink($file);
		}

		return false;
	}

	/**
	 * Удалить все временные файлы
	 */
	public static function clear() {
		$iterator = new RecursiveDirectoryIterator(App::retrieve("path/cache/0", App::get("base_dir") . "/cache") . "/");

		/** @var RecursiveDirectoryIterator $file */
		foreach ($iterator as $file) {
			if ($file->isFile() && substr($file, -6) == ".cache") {
				unlink(App::retrieve("path/cache/0", App::get("base_dir") . "/cache") . "/" . $file->getFilename());
			}
		}
	}
}
