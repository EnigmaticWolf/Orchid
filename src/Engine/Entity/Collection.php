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

use Closure;
use Countable;
use Engine\Orchid;
use Iterator;

abstract class Collection implements Countable, Iterator {
	protected $app;

	protected $type    = null;	// модель
	protected $data    = null;	// данные

	public final function __construct($data = []) {
		$this->app  = &Orchid::getInstance();
	}

	protected final function __destruct() {
		$this->data = null;
	}

	/**
	 * Метод возвращает модель по заданному индексу
	 * @param int $index
	 * @return mixed
	 */
	public function &get($index = 0) {
		return $this->data[$index];
	}

	/**
	 * Метод возвращает склонированный объект текущей коллекции
	 * @return mixed
	 */
	public function getClone() {
		return new $this($this->data);
	}

	/**
	 * Метод удаляет модель по заданному индексу или по экземпляру объекта
	 * @param $val
	 * @return $this
	 */
	public function remove($val) {
		if (is_numeric($val) && $this->data[$val]) {
			unset($this->data[$val]);
		} elseif (get_class($val) == $this->type && ($key = array_search($val, $this->data))) {
			unset($this->data[$key]);
		}

		return $this;
	}

	/**
	 * Метод проверяет пуста ли коллекция
	 * @return bool
	 */
	public function isEmpty() {
		return empty($this->data);
	}

	/**
	 * Метод возвращает количество моделей в коллекции
	 * @return int
	 */
	public function count() {
		return count($this->data);
	}

	/**
	 * Метод очищает коллекцию
	 * @return $this
	 */
	public function clear() {
		$this->data = [];

		return $this;
	}

	/**
	 * Метод устанавливает новое значение для ключа всех моделей
	 * @param $field
	 * @param $val
	 * @return $this
	 */
	public function set($field, $val) {
		foreach ($this->data as $model) {
			$model[$field] = $val;
		}

		return $this;
	}

	/**
	 * Метод собирает значения поля из всех моделей в коллекции
	 * @param      $field
	 * @param null $val
	 * @return array
	 */
	public function collect($field, $val = null) {
		$data = [];

		// $oc->collect('login')
		if ($val === null) {
			foreach ($this->data as $model) {
				$data[] = $model[$field];
			}

			return $data;
		}

		// $oc->collect(['login', 'password'])
		if (is_array($field)) {
			foreach ($this->data as $model) {
				$item = [];
				foreach ($field as $key) {
					$item[$key] = $model[$key];
				}
				$data[] = $item;
			}

			return $data;
		}

		// $oc->collect('key', ['login', 'password'])
		if (is_array($val)) {
			foreach ($this->data as $model) {
				$item = [];
				foreach ($val as $key) {
					$item[$key] = $model[$key];
				}
				$data[$model[$field]] = $item;
			}

			return $data;
		} else {
			// $oc->collect('key', 'login')
			foreach ($this->data as $model) {
				$data[$model[$field]] = $model[$val];
			}

			return $data;
		}
	}

	/**
	 * Найти все модели, параметр которых удовлетворяют условию
	 * @param      $field
	 * @param null $val
	 * @return Collection
	 */
	public function find($field, $val = null) {
		$data = [];

		// $oc->find(function($obj, $key))
		if (!is_string($field) && is_callable($field)) {
			foreach ($this->data as $key => $obj) {
				if ($field($obj, $key)) {
					$data[] = $obj;
				}
			}

			return new $this($data);
		}

		// $oc->find('Location')
		if (func_num_args() == 1) {
			foreach ($this->data as $obj) {
				if (isset($obj[$field])) {
					$data[] = $obj;
				}
			}
		}

		// $oc->find('Location', 'localhost/js')
		if (func_num_args() > 1) {
			foreach ($this->data as $obj) {
				if ($obj[$field] == $val) {
					$data[] = $obj;
				}
			}
		}

		return new $this($data);
	}

	/**
	 * Отфильтровать модели используя замыкание
	 * @param callable $callback
	 * @return $this|Collection
	 */
	public function filter($callback) {
		if (is_callable($callback)) {
			$data = [];
			foreach ($this->data as $index => $model) {
				if ($callback($model, $index)) {
					$data[] = $model;
				}
			}

			return new $this($data);
		}

		return $this;
	}

	/**
	 * Сортирует модели используя замыкание или по указанному полю
	 * @param callback|string $param int callback ( mixed $a, mixed $b )
	 * @param mixed           $args
	 * @return self
	 */
	public function sort($param, $args = null) {
		if (is_string($param)) {
			usort($this->data, $this->sortProperty($param));
		} else {
			if (is_callable($param)) {
				usort($this->data, $this->sortCallable($param, $args));
			}
		}

		return $this;
	}

	/**
	 * Сортировка по свойству
	 * @param string $key
	 * @return Closure
	 */
	protected function sortProperty($key = null) {
		return function ($a, $b) use ($key) {
			return strnatcmp($a->$key, $b->$key);
		};
	}

	/**
	 * Сортировка функцией
	 * @param callable $callable
	 * @param mixed    $args
	 * @return Closure
	 */
	protected function sortCallable($callable, $args = null) {
		return function ($a, $b) use ($callable, $args) {
			return $callable($a, $b, $args);
		};
	}

	/**
	 * Метод возвращает тип коллекций
	 * @return mixed
	 */
	public function getType() {
		return $this->type;
	}

	/**
	 * Метод возвращает модель в виде Массива
	 * @return array
	 */
	public function toArray() {
		return $this->data;
	}

	/**
	 * Метод наполняющий коллекцию моделями
	 */
	abstract public function fetch();

	// Iterator
	public function current() {
		return current($this->data);
	}

	// Iterator
	public function next() {
		return next($this->data);
	}

	// Iterator
	public function prev() {
		return prev($this->data);
	}

	// Iterator
	public function key() {
		return key($this->data);
	}

	// Iterator
	public function valid() {
		return key($this->data) !== null && key($this->data) !== false;
	}

	// Iterator
	public function rewind() {
		reset($this->data);
	}
}