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

use Closure;

abstract class Validator {
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
	public function addRule($validator, $message = '') {
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