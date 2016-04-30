<?php

namespace Orchid\Entity\Validate;

use Closure;

trait Type {
	/**
	 * Проверяемое значение должно быть пустое
	 *
	 * @return Closure
	 */
	public function isEmpty() {
		return function ($field) {
			return empty($field);
		};
	}

	/**
	 * Проверяемое значение должно быть не пустое
	 *
	 * @return Closure
	 */
	public function isNotEmpty() {
		return function ($field) {
			return !empty($field);
		};
	}

	/**
	 * Проверяемое значение должно иметь тип Boolean
	 *
	 * @return Closure
	 */
	public function isBoolean() {
		return function ($field) {
			return is_bool($field);
		};
	}

	/**
	 * Проверяемое значение должно быть числом
	 *
	 * @return Closure
	 */
	public function isNumeric() {
		return function ($field) {
			return is_numeric($field);
		};
	}

	/**
	 * Проверяемое значение должно быть строкой
	 *
	 * @return Closure
	 */
	public function isString() {
		return function ($field) {
			return is_string($field);
		};
	}

	/**
	 * Проверяемое значение будет преобразовано в Boolean
	 * Для значений "1", "true", "on" и "yes" - true
	 * Для значений "0", "false", "off", "no" и "" - false
	 *
	 * @return Closure
	 */
	public function toBoolean() {
		return function (&$field) {
			if (($bool = filter_var($field, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE)) !== null) {
				$field = $bool;
			}

			return true;
		};
	}

	/**
	 * Проверяемое значение будет преобразовано в Integer
	 *
	 * @return Closure
	 */
	public function toInteger() {
		return function (&$field) {
			$field = (int)$field;

			return true;
		};
	}

	/**
	 * Проверяемое значение будет преобразовано в toDouble
	 *
	 * @param int $precision точность округления
	 *
	 * @return Closure
	 */
	public function toDouble($precision = 0) {
		return function (&$field) use ($precision) {
			$field = round((double)$field, $precision);

			return true;
		};
	}

	/**
	 * Проверяемое значение будет преобразовано в String
	 * 
	 * @return Closure
	 */
	public function toString() {
		return function (&$field) {
			$field = (String)$field;

			return true;
		};
	}
}