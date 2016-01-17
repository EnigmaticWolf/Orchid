<?php

namespace Orchid\Entity;

use Closure;
use Countable;
use Iterator;
use SplFixedArray;

abstract class Collection implements Countable, Iterator {
	/**
	 * Полный путь класса модели
	 * @var string|null
	 */
	protected static $model;

	/**
	 * Внутреннее хранилище моделей
	 * @var SplFixedArray
	 */
	protected $data;

	public final function __construct(array $data = []) {
		$this->data = SplFixedArray::fromArray($data);
	}

	/**
	 * Возвращает элемент которому соответствует указанный индекс
	 * @param int $index
	 * @return mixed
	 */
	public function get($index = 0) {
		if (static::$model) {
			return new static::$model($this->data[$index]);
		}

		return $this->data[$index];
	}

	/**
	 * Устанавливает значение элементу
	 * @param $index
	 * @param $data
	 * @return $this
	 */
	public function set($index, $data) {
		if ($data instanceof Model) {
			$this->data[$index] = $data->toArray();
		} else {
			$this->data[$index] = $data;
		}

		return $this;
	}

	/**
	 * Метод собирает значения и возвращает в виде массива
	 *
	 * Собрать значение указанного поля
	 * @usage $oc->collect('login')
	 *
	 * Собрать значения указанных полей
	 * @usage $oc->collect(['login', 'password'])
	 *
	 * Собрать значение указанного поля
	 * Ключём будет значение поля id
	 * @usage $oc->collect('id', 'login')
	 *
	 * Собрать значения указанных полей
	 * Ключём будет значение поля id
	 * @usage $oc->collect('id', ['login', 'password'])
	 *
	 * @param      $field
	 * @param null $value
	 * @return array
	 */
	public function collect($field, $value = null) {
		$data = [];

		// $oc->collect('login')
		if (is_string($field) && is_null($value)) {
			foreach ($this->data as $model) {
				$data[] = $model[$field];
			}
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
		}

		// $oc->collect('id', 'login')
		if (is_string($field) && is_string($value)) {
			foreach ($this->data as $model) {
				$data[$model[$field]] = $model[$value];
			}
		}

		// $oc->collect('id', ['login', 'password'])
		if (is_string($field) && is_array($value)) {
			foreach ($this->data as $model) {
				$item = [];
				foreach ($value as $key) {
					$item[$key] = $model[$key];
				}
				$data[$model[$field]] = $item;
			}
		}

		return $data;
	}

	/**
	 * Найти все модели, параметр которых удовлетворяют условию
	 *
	 * Найти все модели где указанное поле не пустое
	 * @usage $oc->find('Location')
	 *
	 * Найти все модели где указанное поле равно указанному значению
	 * @usage $oc->find('Location', 'Lviv')
	 *
	 * @param      $field
	 * @param null $value
	 * @return Collection
	 */
	public function find($field, $value = null) {
		$data = [];

		// $oc->find('Location')
		if (is_string($field) && is_null($value)) {
			foreach ($this->data as $obj) {
				if (!empty($obj[$field])) {
					$data[] = $obj;
				}
			}
		}

		// $oc->find('Location', 'Lviv')
		if (is_string($field) && !is_null($value)) {
			foreach ($this->data as $obj) {
				if ($obj[$field] == $value) {
					$data[] = $obj;
				}
			}
		}

		return new $this($data);
	}

	/**
	 * Отфильтровать модели используя замыкание
	 * @param callable $callback
	 * @return Collection
	 */
	public function filter($callback) {
		$data = [];

		if (is_callable($callback)) {
			foreach ($this->data as $key => $model) {
				if ($callback($model, $key)) {
					$data[] = $model;
				}
			}
		}

		return new $this($data);
	}

	/**
	 * Сортирует модели
	 *
	 * Сортирует модели по указанному полю
	 * @usage $oc->sort('id')
	 *
	 * Сортирует модели с применением пользовательской функции
	 * @usage $oc->sort(callback(mixed $a, mixed $b, $args))
	 *
	 * @param callback|string $param
	 * @param mixed           $args
	 * @return self
	 */
	public function sort($param, $args = null) {
		$success = false;
		$data = $this->data->toArray(); // получаем все модели

		if (is_string($param)) {
			$success = usort($data, $this->sortProperty($param));
		} elseif (is_callable($param)) {
			$success = usort($data, $this->sortCallable($param, $args));
		}

		// если успешно отсортировали, создаём новый объект
		if ($success) {
			$this->data = SplFixedArray::fromArray($data);
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
			return strnatcmp($a[$key], $b[$key]);
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
	 * Возвращает коллекцию в виде массива
	 * @return array
	 */
	public function toArray() {
		return $this->data->toArray();
	}

	/**
	 * Возвращает текущий элемент массива
	 * @return mixed
	 */
	public function current() {
		if (static::$model) {
			return new static::$model($this->data->current());
		}

		return $this->data->current();
	}

	/**
	 * Перемещение вперед к следующему элементу
	 */
	public function next() {
		$this->data->next();

		return $this;
	}

	/**
	 * Возвращает ключ текущего элемента
	 * @return mixed
	 */
	public function key() {
		return $this->data->key();
	}

	/**
	 * Проверяет текущее положение итератора
	 * @return boolean
	 */
	public function valid() {
		return $this->data->valid();
	}

	/**
	 * Устанавливает итератор на первый элемент
	 */
	public function rewind() {
		$this->data->rewind();

		return $this;
	}

	/**
	 * Возвращает количество элементов объекта
	 * @return int
	 */
	public function count() {
		return $this->data->count();
	}
}