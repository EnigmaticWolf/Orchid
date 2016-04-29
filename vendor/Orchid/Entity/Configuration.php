<?php

namespace Orchid\Entity;

use Orchid\App;
use Orchid\Entity\Exception\FileNotFoundException;
use Orchid\Entity\Exception\RuntimeException;

class Configuration {
	protected $item;

	/**
	 * Инициализация из указанного файла
	 *
	 * @param $path
	 *
	 * @return bool|static
	 * @throws FileNotFoundException
	 */
	public static function fromFile($path) {
		if ($path = App::getPath($path)) {
			$ext = pathinfo($path);

			switch ($ext["extension"]) {
				case "ini": {
					return new static((array)parse_ini_file($path, true));
				}
				case "php": {
					return new static((array)require_once $path);
				}
			}
		}

		throw new FileNotFoundException("Не удалось найти файл конфигурации");
	}

	/**
	 * Инициализация из переданного массива
	 *
	 * @param array $data
	 *
	 * @return bool|static
	 * @throws RuntimeException
	 */
	public static function fromArray(array $data) {
		if (!empty($data)) {
			return new static($data);
		}

		throw new RuntimeException("Переданный массив пуст");
	}

	protected function __construct(array $data) {
		$this->item = $data;
	}

	/**
	 * Получение ключа из конфигурации
	 *
	 * @param      $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function get($key, $default = null) {
		if (isset($this->item[$key])) {
			return $this->item[$key];
		}

		return $default;
	}
}
