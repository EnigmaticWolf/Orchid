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
use function Orchid\fetch_from_array;

class Session extends Extension {
	/**
	 * Создание новой сессии с заданным именем
	 * @param null $sessionName
	 */
	public static function create($sessionName = null) {
		if (!strlen(session_id())) {
			$name = $sessionName ? $sessionName : App::get("session");

			session_name($name);
			session_start();
		}
	}

	/**
	 * Запись данных в текущую сессию
	 * @param string $key ключевое слово
	 * @param string $value значение для записи
	 */
	public static function write($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Чтение данных из текущей сессии по заданному ключу
	 * @param string $key ключевое слово
	 * @param string $default значение для записи
	 * @return Mixed
	 */
	public static function read($key, $default = null) {
		return fetch_from_array($_SESSION, $key, $default);
	}

	/**
	 * Удаление данных из текущей сессии по заданному ключу
	 * @param string $key ключевое слово
	 */
	public static function delete($key) {
		unset($_SESSION[$key]);
	}

	/**
	 * Уничтожение текущей сессии
	 */
	public static function destroy() {
		session_destroy();
	}
}
