<?php

/*
 * Copyright (c) 2011-2014 AEngine
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

namespace Orchid\Extension;

use Orchid\App;
use Orchid\Entity\Extension;

class Crypta extends Extension {
	/**
	 * Зашифровать строку
	 * @param string $input строка
	 * @return string зашифрованная строка
	 */
	public static function encrypt($input) {
		return base64_encode(static::crypt($input));
	}

	/**
	 * Дешифровать строку
	 * @param string $input строка
	 * @return string расшифрованная строка
	 */
	public static function decrypt($input) {
		return static::crypt(base64_decode($input));
	}

	/**
	 * Вспомогательный метод для работы со строкой строку
	 * @param string $input строка
	 * @return string обработанная строка
	 */
	protected static function crypt($input) {
		$salt = md5(App::get("secret"));
		$len = strlen($input);
		$gamma = "";
		$n = $len > 100 ? 8 : 2;
		while (strlen($gamma) < $len) {
			$gamma .= substr(pack("H*", sha1($gamma . $salt)), 0, $n);
		}

		return $input ^ $gamma;
	}

	/**
	 * Сгенерировать хешсумму для строки
	 * @param string $string строка из которой получить хешсумму
	 * @return string хешсумма
	 */
	public static function hash($string) {
		$salt = substr(hash("whirlpool", uniqid(rand() . App::get("secret"), true)), 0, 12);
		$hash = hash("whirlpool", $salt . $string);
		$saltPos = (strlen($string) >= strlen($hash) ? strlen($hash) : strlen($string));

		return substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
	}

	/**
	 * Проверить строку на соответствие хешсумме
	 * @param string $string проаеряемая строка
	 * @param string $hashString хешсумма
	 * @return Boolean
	 */
	public static function check($string, $hashString) {
		$saltPos = (strlen($string) >= strlen($hashString) ? strlen($hashString) : strlen($string));
		$salt = substr($hashString, $saltPos, 12);
		$hash = hash("whirlpool", $salt . $string);

		return $hashString == substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
	}
}