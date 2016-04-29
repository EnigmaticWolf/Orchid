<?php

namespace Orchid\Entity;

use Orchid\App;

class Configuration {
	protected $item;

	/**
	 * Инициализация из указанного файла
	 *
	 * @param $path
	 *
	 * @return bool|static
	 */
	public static function fromFile($path) {
		if ($path = App::getPath($path)) {
			$ext = pathinfo($path);

			switch ($ext["extension"]){
				case "ini": {
					return new static((array)parse_ini_file($path, true));
				}
				case "php": {
					return new static((array)require_once $path);
				}
			}
		}

		return false;
	}

	/**
	 * Инициализация из переданного массива
	 *
	 * @param array $data
	 *
	 * @return bool|static
	 */
	public static function fromArray(array $data) {
		if (!empty($data)) {
			return new static($data);
		}

		return false;
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
