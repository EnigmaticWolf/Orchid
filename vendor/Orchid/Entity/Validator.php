<?php

namespace Orchid\Entity;

use Closure;

class Validator {
	protected $data  = [];
	protected $field = null;
	protected $rule  = [];
	protected $error = [];

	public function __construct(array &$data) {
		$this->data = &$data;
	}

	/**
	 * Выбирает обязательное поле для валидации
	 * @param string $field
	 * @return $this
	 */
	public function attr($field) {
		$this->field = $field;

		return $this;
	}

	/**
	 * Выбирает НЕобязательное поле для валидации
	 * @param string $field
	 * @return $this
	 */
	public function option($field) {
		$this->field = null;

		if (!empty($this->data[$field])) {
			$this->field = $field;
		}

		return $this;
	}

	/**
	 * Добавляет к выбранному полю правило валидации
	 * @param Closure $validator
	 * @param string  $message
	 * @return $this
	 */
	public function addRule($validator, $message = "") {
		if ($this->field) {
			$this->rule[$this->field][] = [
				"validator" => $validator,
				"message"   => $message,
			];
		}

		return $this;
	}

	/**
	 * Выполняет операции валидации полей по заданным правилам
	 * @return array|bool
	 */
	public function validate() {
		$this->error = [];

		foreach ($this->rule as $field => $rules) {
			foreach ($rules as $rule) {
				if ($rule["validator"]($this->data[$field]) !== true) {
					$this->error[$field] = $rule["message"] ? $rule["message"] : false;
					break;
				}
			}
		}

		return $this->error ? $this->error : true;
	}
}