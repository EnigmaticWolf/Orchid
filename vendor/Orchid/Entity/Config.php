<?php

namespace Orchid\Entity;

use ArrayAccess;
use Iterator;
use RuntimeException;
use Orchid\Entity\Exception\FileNotFoundException;

class Config implements ArrayAccess, Iterator {
	protected $position = 0;
	protected $array;

	/**
	 * Инициализация из указанного файла
	 *
	 * @param string $path
	 *
	 * @return static
	 * @throws FileNotFoundException
	 */
	public static function fromFile($path) {
		if (file_exists($path)) {
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
	 * @return static
	 */
	public static function fromArray(array $data) {
		return new static($data);
	}

	/**
	 * @param array $data
	 *
	 * @throws RuntimeException
	 */
	protected function __construct(array $data) {
		$this->array = $data;
	}

	/**
	 * Получение ключа из конфигурации
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function get($key, $default = null) {
		if (isset($this->array[$key])) {
			return $this->array[$key];
		}

		return $default;
	}

	/* Методы ArrayAccess */

	public function offsetGet($key) {
		return $this->array[$key];
	}

	public function offsetSet($key, $value) {
		throw new RuntimeException("Не возможно изменить значение конфигурации");
	}

	public function offsetExists($key) {
		return isset($this->array[$key]);
	}

	public function offsetUnset($key) {
		throw new RuntimeException("Не возможно удалить значение конфигурации");
	}

	/* Методы Iterator */

	public function current() {
		return $this->array[$this->position];
	}

	public function next() {
		$this->position++;
	}

	public function prev() {
		$this->position--;
	}

	public function key() {
		return $this->position;
	}

	public function valid() {
		return isset($this->array[$this->position]);
	}

	public function rewind() {
		$this->position = 0;
	}
}
