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

namespace Engine\Entity\Validate;

trait Type {
	public function isEmpty() {
		return function ($field) {
			return empty($field);
		};
	}

	public function isNotEmpty() {
		return function ($field) {
			return !empty($field);
		};
	}

	public function isBoolean() {
		return function ($field) {
			return is_bool($field);
		};
	}

	public function isNumeric() {
		return function ($field) {
			return is_numeric($field);
		};
	}

	public function isString() {
		return function ($field) {
			return is_string($field);
		};
	}

	public function isEmail() {
		return function ($field) {
			return !!filter_var($field, FILTER_VALIDATE_EMAIL);
		};
	}

	public function isIp() {
		return function ($field) {
			return !!filter_var($field, FILTER_VALIDATE_IP);
		};
	}

	/**
	 * Приводит значение к boolean
	 */
	public function toBoolean() {
		return function (&$field) {
			$field = filter_var($field, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
			return true;
		};
	}
}