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

namespace Engine\Entity;

use Engine\Orchid;

abstract class Model {
	protected        $app;

	protected static $default  = [];	// поля модели
	protected        $data     = [];	// данные модели
	protected        $previous = [];	// предыдущие значения

	public final function __construct($data = []) {
		$this->app  = &Orchid::getInstance();
		$this->data = static::$default;
		$this->setAll($data);
	}

	protected final function __destruct() {
		$this->data = $this->previous = null;
	}

	/**
	 * Устанавливает значение для ключа
	 * @param $key
	 * @param $val
	 * @return $this
	 */
	public function set($key, $val = null) {
		if (is_array($key)) {
			return $this->setAll($key);
		}
		if (array_key_exists($key, static::$default)) {
			if ($this->data[$key]) {
				$this->previous[$key] = $this->data[$key];
			}
			$this->data[$key] = $val;
		}

		return $this;
	}

	/**
	 * Устанавливает значения для всех ключей
	 * @param array $data
	 * @return $this
	 */
	protected function setAll(array $data) {
		foreach ($data as $key => $val) {
			$this->set($key, $val);
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
	 * Возвращает склонированный объект текущей модели
	 * @return mixed
	 */
	public function getClone() {
		return new $this($this->data);
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
	 * Восстанавливает значение ключа по умолчанию
	 * @param $key
	 * @return $this
	 */
	public function delete($key) {
		if ($this->data[$key]) {
			$this->previous[$key] = $this->data[$key];
		}
		$this->data[$key] = static::$default[$key];

		return $this;
	}

	/**
	 * Восстанавливает предыдущее значение ключа или всей модели
	 * @param null $key
	 * @return $this
	 */
	public function revert($key = null) {
		if ($key) {
			$this->data[$key] = $this->previous[$key];
		} else {
			$this->data = $this->previous;
		}

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

	/**
	 * Получает информацию о модели из внешнего хранилища
	 * @return $this
	 */
	public function read() {
		return $this;
	}

	/**
	 * Обёртка вокруг защищённых методов insert & update
	 * @return $this
	 */
	public function save() {
		return $this;
	}

	/**
	 * Читает модель во внешнем хранилище
	 * @return $this
	 */
	abstract protected function select();

	/**
	 * Создаёт модель во внешнем хранилище
	 * @return $this
	 */
	abstract protected function insert();

	/**
	 * Обновляет модель во внешнем хранилище
	 * @return $this
	 */
	abstract protected function update();

	/**
	 * Удаляет модель во внешнем хранилище
	 * @return $this
	 */
	abstract protected function remove();
}