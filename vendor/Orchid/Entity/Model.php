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

namespace Orchid\Entity;

abstract class Model {
	/**
	 * Массив описания полей модели
	 * @var array
	 */
	protected static $default = [];

	/**
	 * Массив данных модели
	 * @var array
	 */
	protected $data = [];

	public final function __construct(array $data = []) {
		$this->setAll(array_merge(static::$default, $data));
	}

	/**
	 * Устанавливает значение для ключа
	 * @param $key
	 * @param $value
	 * @return $this
	 */
	public function set($key, $value = null) {
		if (array_key_exists($key, static::$default)) {
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
		return static::$default === $this->data;
	}

	/**
	 * Восстанавливает значение ключа по умолчанию
	 * @param $key
	 * @return $this
	 */
	public function delete($key) {
		$this->data[$key] = static::$default[$key];

		return $this;
	}

	/**
	 * Восстанавливает значения модели по умолчанию
	 * @return $this
	 */
	public function clear() {
		$this->data = static::$default;

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