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

abstract class Validator {
	protected $data  = [];
	protected $field = null;
	protected $rule  = [];
	protected $error = [];

	public function __construct(&$data) {
		$this->data = &$data;
	}

	/**
	 * Выбирает обязательное поле для валидации
	 * @param $field
	 * @return $this
	 */
	public function attr($field) {
		$this->field = $field;

		return $this;
	}

	/**
	 * Выбирает опциональное поле для валидации
	 * @param $field
	 * @return $this
	 */
	public function option($field) {
		$this->field = null;

		if (!empty($this->data[$field])) {
			$this->attr($field);
		}

		return $this;
	}

	/**
	 * Добавляет к выбранному полю правило валидации
	 * @param      $validator
	 * @param null $reason
	 * @return $this
	 */
	public function addRule($validator, $reason = null) {
		if (!is_null($this->field)) {
			$this->rule[$this->field][] = [
				"validator" => $validator,
				"reason"    => $reason,
			];
		}

		return $this;
	}

	/**
	 * Выполняет операции валидации полей по заданным правилам
	 * @return array|bool
	 */
	public function validate() {
		foreach ($this->rule as $key => $val) {
			foreach ($val as $rule) {
				if (isset($this->data[$key])) {
					if (!isset($this->error[$key]) && $rule["validator"]($this->data[$key]) !== true) {
						$this->error[$key] = $rule["reason"] ? $rule["reason"] : false;
					}
				} else {
					$this->error[$key] = false;
				}
			}
		}

		return $this->error ? $this->error : true;
	}

}