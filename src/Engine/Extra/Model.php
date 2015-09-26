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

namespace Engine\Extra;

use Engine\Orchid;

abstract class Model {
	protected $app;
	protected $default  = [];
	protected $primary  = null;
	protected $data     = [];
	protected $previous = [];

	public final function __construct($data = []) {
		$this->app  = &Orchid::getInstance();
		$this->data = $this->default;
		$this->setAll($data);
	}

	/**
	 * Метод устанавливает новое значение для ключа
	 * @param $key
	 * @param $val
	 * @return $this
	 */
	public function set($key, $val = null) {
		if (is_array($key)) {
			return $this->setAll($key);
		}
		if (array_key_exists($key, $this->default)) {
			if ($this->data[$key]) {
				$this->previous[$key] = $this->data[$key];
			}
			$this->data[$key] = $val;
		}

		return $this;
	}

	/**
	 * Метод устанавливает значения для всех кд.чей
	 * @param array $data
	 * @return $this
	 */
	protected function setAll(array $data) {
		foreach($data as $key => $val) {
			$this->set($key, $val);
		}

		return $this;
	}

	/**
	 * Метод получает значение по ключу
	 * @param $key
	 * @return mixed
	 */
	public function get($key) {
		return $this->data[$key];
	}

	/**
	 * Метод возвращает склонированный объект текущей модели
	 * @return mixed
	 */
	public function getClone() {
		return new $this($this->data);
	}

	/**
	 * Метод проверяет наличие ключа
	 * @param $key
	 * @return bool
	 */
	public function exist($key) {
		return isset($this->data[$key]);
	}

	/**
	 * Метод восстанавливает значение ключа по умолчанию
	 * @param $key
	 * @return $this
	 */
	public function delete($key) {
		if ($this->data[$key]) {
			$this->previous[$key] = $this->data[$key];
		}
		$this->data[$key] = $this->default[$key];

		return $this;
	}

	/**
	 * Метод восстанавливает предыдущее значение ключа или всей модели
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
	 * Метод восстанавливает значения модели поумолчанию
	 * @return $this
	 */
	public function clear() {
		$this->data = $this->default;

		return $this;
	}

	/**
	 * Метод возвращает первичный ключ модели
	 * @return mixed
	 */
	public function getPrimary() {
		return $this->primary;
	}

	/**
	 * Метод возвращает модель в виде Массива
	 * @return array
	 */
	public function toArray() {
		return $this->data;
	}

	/**
	 * Метод возвращает модель в виде JSON объекта
	 * @return string
	 */
	public function toJSON() {
		return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
	}

	/**
	 * Метод получает информацию о модели из внешнего хранилища
	 * @return $this
	 */
	public function read() {
		return $this;
	}

	/**
	 * Метод обёртка вокруг защищённых методов insert & update
	 * @return $this
	 */
	public function save() {
		return $this;
	}

	/**
	 * Метод возвращает массив полей модели
	 * @return array
	 */
	public function getMask() {
		return $this->default;
	}

	/**
	 * Метод создаёт модель во внешнем хранилище
	 */
	abstract protected function insert();

	/**
	 * Метод обновляет модель во внешнем хранилище
	 */
	abstract protected function update();

	/**
	 * Метод удаляет модель во внешнем хранилище
	 * @return null
	 */
	public function remove() {
		$this->data     = $this->default;
		$this->previous = $this->default;

		return null;
	}
}