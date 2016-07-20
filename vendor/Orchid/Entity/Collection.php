<?php

namespace Orchid\Entity;

use Closure;
use Countable;
use Iterator;
use SplFixedArray;

abstract class Collection implements Countable, Iterator {
	/**
	 * Full path of the model class
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Internal storage models
	 *
	 * @var SplFixedArray
	 */
	protected $data;

	public final function __construct(array $data = []) {
		$this->data = SplFixedArray::fromArray($data);
	}

	/**
	 * Returns element that corresponds to the specified index
	 *
	 * @param int $index
	 *
	 * @return mixed
	 */
	public function get($index = 0) {
		if (static::$model) {
			return new static::$model($this->data[$index]);
		}

		return $this->data[$index];
	}

	/**
	 * Set value of the element
	 *
	 * @param int         $index
	 * @param Model|array $data
	 *
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
	 * Collects and returns the values as array
	 *
	 * Collect value of the specified field
	 * @usage $oc->collect('login')
	 *
	 * Collect values of these fields
	 * @usage $oc->collect(['login', 'password'])
	 *
	 * Collect value of the specified field
	 * The key is 'id' field value
	 * @usage $oc->collect('id', 'login')
	 *
	 * Collect values of these fields
	 * The key is 'id' field value
	 * @usage $oc->collect('id', ['login', 'password'])
	 *
	 * @param string|array $field
	 * @param string|array $value
	 *
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
	 * Find all model parameter satisfy the condition
	 *
	 * Find all model wherein the field is not empty
	 * @usage $oc->find('Location')
	 *
	 * Find all model wherein the field is equal to the specified value
	 * @usage $oc->find('Location', 'Lviv')
	 *
	 * @param string $field
	 * @param string $value
	 *
	 * @return $this
	 */
	public function find($field, $value = null) {
		$data = [];

		if (is_null($value)) {
			// $oc->find('Location')
			if (is_string($field)) {
				foreach ($this->data as $obj) {
					if (!empty($obj[$field])) {
						$data[] = $obj;
					}
				}
			}
		} else {
			// $oc->find('Location', 'Lviv')
			if (is_string($field)) {
				foreach ($this->data as $obj) {
					if ($obj[$field] == $value) {
						$data[] = $obj;
					}
				}
			}
		}

		return new $this($data);
	}

	/**
	 * Filter models using user-defined function
	 *
	 * @param Closure $callable
	 *
	 * @return $this
	 */
	public function filter($callable) {
		$data = [];

		if (is_callable($callable)) {
			foreach ($this->data as $key => $model) {
				if ($callable($model, $key)) {
					$data[] = $model;
				}
			}
		}

		return new $this($data);
	}

	/**
	 * Sort models
	 *
	 * Sort models for the specified field
	 * @usage $oc->sort('id')
	 *
	 * Sort models with user-defined function
	 * @usage $oc->sort(function(mixed $a, mixed $b, $args))
	 *
	 * @param Closure|string $param
	 * @param mixed          $args
	 *
	 * @return $this
	 */
	public function sort($param, $args = null) {
		$success = false;
		$data = $this->data->toArray(); // get all the models

		if (is_string($param)) {
			$success = usort($data, $this->sortProperty($param));
		} elseif (is_callable($param)) {
			$success = usort($data, $this->sortCallable($param, $args));
		}

		// if successfully sorted, create a new object
		if ($success) {
			$this->data = SplFixedArray::fromArray($data);
		}

		return $this;
	}

	/**
	 * Sort by property
	 *
	 * @param string $key
	 *
	 * @return Closure
	 */
	protected function sortProperty($key = null) {
		return function ($a, $b) use ($key) {
			return strnatcmp($a[$key], $b[$key]);
		};
	}

	/**
	 * Sort function
	 *
	 * @param Closure $callable
	 * @param mixed    $args
	 *
	 * @return Closure
	 */
	protected function sortCallable($callable, $args = null) {
		return function ($a, $b) use ($callable, $args) {
			return $callable($a, $b, $args);
		};
	}

	/**
	 * Returns collection as an array
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->data->toArray();
	}

	/**
	 * Returns current element of the array
	 *
	 * @return mixed
	 */
	public function current() {
		if (static::$model) {
			return new static::$model($this->data->current());
		}

		return $this->data->current();
	}

	/**
	 * Move forward to next element
	 *
	 * @return $this
	 */
	public function next() {
		$this->data->next();

		return $this;
	}

	/**
	 * Returns current element key
	 *
	 * @return mixed
	 */
	public function key() {
		return $this->data->key();
	}

	/**
	 * Check current position of the iterator
	 *
	 * @return boolean
	 */
	public function valid() {
		return $this->data->valid();
	}

	/**
	 * Set iterator to the first element
	 *
	 * @return mixed
	 */
	public function rewind() {
		$this->data->rewind();

		return $this;
	}

	/**
	 * Returns number of elements of the object
	 *
	 * @return int
	 */
	public function count() {
		return $this->data->count();
	}
}