<?php

namespace Orchid\Entity;

abstract class Model {
	/**
	 * Массив описания полей модели
	 * @var array
	 */
	protected static $field = [];

	/**
	 * Массив данных модели
	 * @var array
	 */
	protected $data = [];

	public final function __construct(array $data = []) {
		$this->setAll(array_merge(static::$field, $data));
	}

	/**
	 * Устанавливает значение для ключа
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function set($key, $value = null) {
		if (array_key_exists($key, static::$field)) {
			$this->data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Устанавливает значения для всех ключей
	 * @param array $data
	 * @return $this
	 */
	public function setAll(array $data) {
		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}

		return $this;
	}

	/**
	 * Получает значение по ключу
	 * @param $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->data[$key];
	}

	/**
	 * Проверяет наличие ключа
	 * @param $key
	 * @return bool
	 */
	public function exist($key) {
		return isset($this->data[$key]);
	}

	/**
	 * Проверяет пустая ли модель
	 * @return bool
	 */
	public function isEmpty() {
		return static::$field === $this->data;
	}

	/**
	 * Восстанавливает значение ключа по умолчанию
	 * @param $key
	 * @return $this
	 */
	public function delete($key) {
		$this->data[$key] = static::$field[$key];

		return $this;
	}

	/**
	 * Восстанавливает значения модели по умолчанию
	 * @return $this
	 */
	public function clear() {
		$this->data = static::$field;

		return $this;
	}

	/**
	 * Возвращает модель в виде Массива
	 * @return array
	 */
	public function toArray() {
		return $this->data;
	}
}