<?php

namespace Orchid\Entity;

abstract class Model {
	/**
	 * Array of fields describing the model
	 *
	 * @var array
	 */
	protected static $field = [];

	/**
	 * Model data array
	 *
	 * @var array
	 */
	protected $data = [];

	public final function __construct(array $data = []) {
		$this->setAll(array_merge(static::$field, $data));
	}

	/**
	 * Set value for a key
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return $this
	 */
	public function set($key, $value = null) {
		if (array_key_exists($key, static::$field)) {
			$this->data[$key] = $value;
		}

		return $this;
	}

	/**
	 * Set values for all keys
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setAll(array $data) {
		foreach ($data as $key => $value) {
			$this->set($key, $value);
		}

		return $this;
	}

	/**
	 * Return value for a key
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return $this->data[$key] ?? static::$field[$key];
	}

	/**
	 * Checks for key exists
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function exist($key) {
		return $this->data[$key] ?? false;
	}

	/**
	 * Checks whether the model is empty
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return static::$field === $this->data;
	}

	/**
	 * Restores default value for key
	 *
	 * @param string $key
	 *
	 * @return $this
	 */
	public function delete($key) {
		$this->data[$key] = static::$field[$key];

		return $this;
	}

	/**
	 * Restores default model data
	 *
	 * @return $this
	 */
	public function clear() {
		$this->data = static::$field;

		return $this;
	}

	/**
	 * Returns model as array
	 *
	 * @return array
	 */
	public function toArray() {
		return $this->data;
	}
}