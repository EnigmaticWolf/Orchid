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

namespace Engine\Extension;

use Engine\Extension;

class Crypta extends Extension {
	protected $salt = null;

	public function initialize() {
		$this->salt = $this->app["secret"];
	}

	/**
	 * Метод возвращает зашифрованную строку
	 * @param String $input строка для шифрования
	 * @return String зашифрованная строка
	 */
	public function encrypt($input) {
		return base64_encode($this->crypt($input));
	}

	/**
	 * Метод возвращает дешифрованную строку
	 * @param String $input строка для дешифрации
	 * @return String расшифрованная строка
	 */
	public function decrypt($input) {
		return $this->crypt(base64_decode($input));
	}

	/**
	 * Приватный метод возвращает строку
	 * @param String $input строка
	 * @return String обработанная строка
	 */
	protected function crypt($input) {
		$salt  = md5($this->salt);
		$len   = strlen($input);
		$gamma = "";
		$n     = $len > 100 ? 8 : 2;
		while (strlen($gamma) < $len) {
			$gamma .= substr(pack("H*", sha1($gamma . $salt)), 0, $n);
		}

		return $input ^ $gamma;
	}

	/**
	 * Метод возвращает хешсумму для строки
	 * @param String $string строка из которой получить хешсумму
	 * @return String хешсумма
	 */
	public function hash($string) {
		$salt    = substr(hash("whirlpool", uniqid(rand() . $this->salt, true)), 0, 12);
		$hash    = hash("whirlpool", $salt . $string);
		$saltPos = (strlen($string) >= strlen($hash) ? strlen($hash) : strlen($string));

		return substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
	}

	/**
	 * Метод проверяет строку на соответствие хешсумме
	 * @param String $string     проаеряемая строка
	 * @param String $hashString хешсумма
	 * @return Boolean
	 */
	public function check($string, $hashString) {
		$saltPos = (strlen($string) >= strlen($hashString) ? strlen($hashString) : strlen($string));
		$salt    = substr($hashString, $saltPos, 12);
		$hash    = hash("whirlpool", $salt . $string);

		return $hashString == substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
	}
}