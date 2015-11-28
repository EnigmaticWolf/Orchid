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

namespace Orchid\Entity\Validate;

use Closure;

trait Base {
	public static $DATE_RU = "d.m.Y"; // формат даты Русский (ГОСТ Р 6.30-2003 (п. 3.11))
	public static $DATE_EN = "d-m-Y"; // формат даты Английский
	public static $DATE_US = "m-d-Y"; // формат даты США
	public static $DATE_DB = "Y-m-d"; // формат даты баз данных (ISO 8601)

	/**
	 * Проверяемое значение будет преобразовано в дату в указанном формате
	 * @param string $format
	 * @return Closure
	 */
	public function date($format = "") {
		return function (&$field) use ($format) {
			if (($time = strtotime($field)) !== false) {
				$field = date($format, $time);

				return true;
			}

			return false;
		};
	}

	/**
	 * Проверяемое значение должно быть больше или равно указанному
	 * В случае если проверяемое значение - строка, проверяется длинна строки
	 * @param int $min
	 * @return Closure
	 */
	public function min($min = -INF) {
		return function ($field) use ($min) {
			if (is_string($field)) {
				return mb_strlen($field) >= $min;
			}
			if (is_numeric($field)) {
				return $field >= $min;
			}

			return false;
		};
	}

	/**
	 * Проверяемое значение должно быть меньше или равно указанному
	 * В случае если проверяемое значение - строка, проверяется длинна строки
	 * @param int $max
	 * @return Closure
	 */
	public function max($max = INF) {
		return function ($field) use ($max) {
			if (is_string($field)) {
				return mb_strlen($field) <= $max;
			}
			if (is_numeric($field)) {
				return $field <= $max;
			}

			return false;
		};
	}
}