<?php

namespace Orchid\Entity\Validate;

use Closure;

trait Str {
	/**
	 * Экранирует строку
	 *
	 * @return Closure
	 */
	public function escape() {
		return function (&$field) {
			$field = str_replace(
				["'", '"', ">", "<", "`", "\\"],
				["&#039;", "&#34;", "&#62;", "&#60;", "&#96;", "&#92;"],
				$field
			);

			return true;
		};
	}

	/**
	 * Проверяемое значение это E-Mail адрес
	 *
	 * @return Closure
	 */
	public function isEmail() {
		return function ($field) {
			return !!filter_var($field, FILTER_VALIDATE_EMAIL);
		};
	}

	/**
	 * Проверяемое значение это IP адрес
	 * 
	 * @return Closure
	 */
	public function isIp() {
		return function ($field) {
			return !!filter_var($field, FILTER_VALIDATE_IP);
		};
	}
}