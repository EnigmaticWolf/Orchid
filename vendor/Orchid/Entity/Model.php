<?php

namespace Orchid\Entity;

use Orchid\App;

abstract class Model {
	/**
	 * @var App
	 */
	protected $app;

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
		$this->app = App::getInstance();
		$this->setAll(array_merge(static::$field, $data));
	}

	/**
	 * Set the value for a key
	 *
	 * @param $key
	 * @param $value
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
	 * Set the values for all keys
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
	 * Return the value for a key
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function get($key) {
		return $this->data[$key];
	}

	/**
	 * Checks for key exists
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function exist($key) {
		return isset($this->data[$key]);
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
	 * Restores the default value for key
	 *
	 * @param $key
	 *
	 * @return $this
	 */
	public function delete($key) {
		$this->data[$key] = static::$field[$key];

		return $this;
	}

	/**
	 * Restores the default model data
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