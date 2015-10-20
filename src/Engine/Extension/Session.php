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
use function engine\fetch_from_array;

class Session extends Extension {
	protected $initialized = false;
	public    $name;

	/**
	 * Создание новой сессии с заданным именем
	 * @param null $sessionName
	 */
	public function create($sessionName = null) {
		if ($this->initialized) {
			return;
		}
		if (!strlen(session_id())) {
			$this->name = $sessionName ? $sessionName : $this->app["session"];

			session_name($this->name);
			session_start();
		} else {
			$this->name = session_name();
		}
		$this->initialized = true;
	}

	/**
	 * Запись данных в текущую сессию
	 * @param string $key ключевое слово
	 * @param string $value значение для записи
	 */
	public function write($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Чтение данных из текущей сессии по заданному ключу
	 * @param string $key ключевое слово
	 * @param string $default значение для записи
	 * @return Mixed
	 */
	public function read($key, $default = null) {
		return fetch_from_array($_SESSION, $key, $default);
	}

	/**
	 * Удаление данных из текущей сессии по заданному ключу
	 * @param string $key ключевое слово
	 */
	public function delete($key) {
		unset($_SESSION[$key]);
	}

	/**
	 * Уничтожение текущей сессии
	 */
	public function destroy() {
		session_destroy();
	}
}
