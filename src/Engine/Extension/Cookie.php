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

namespace Engine\Extension;

use Engine\Extension;

class Cookie extends Extension {
	/** @var array */
	protected $_cookies = [];

	/** @var array */
	protected $_deleted_cookies = [];

	/**
	 * Метод устанавливает новую cookie
	 * @param string $name      название
	 * @param string $value     значение
	 * @param mixed  $ttl       время жизни
	 * @param string $path      путь
	 * @param string $domain    домен
	 * @param bool   $secure    только HTTPS
	 * @param bool   $http_only только HTTP
	 * @return bool
	 */
	public function set($name, $value, $ttl = 86400 /* 1 день */, $path = "/", $domain = "", $secure = false, $http_only = false) {
		$this->_cookies[$name] = $value;
		$result                = setcookie($name, $value, time() + $ttl, $path, $domain, $secure, $http_only);
		if (isset($this->_deleted_cookies[$name])) {
			unset($this->_deleted_cookies[$name]);
		}

		return $result;
	}

	/**
	 * Метод получает значение cookie
	 * @param string $name название
	 * @return mixed
	 */
	public function get($name) {
		if (isset($this->_deleted_cookies[$name])) {
			return null;
		}
		if (array_key_exists($name, $this->_cookies)) {
			return $this->_cookies[$name];
		}
		$value                 = isset($_COOKIE[$name]) ? $_COOKIE[$name] : null;
		$this->_cookies[$name] = $value;

		return $value;
	}

	/**
	 * Метод удаляет cookie
	 * @param string $name      название
	 * @param string $path      путь
	 * @param string $domain    домен
	 * @param bool   $secure    только HTTPS
	 * @param bool   $http_only только HTTP
	 * @return bool
	 */
	public function delete($name, $path = "/", $domain = "", $secure = false, $http_only = false) {
		$success                       = $this->set($name, null, -10, $path, $domain, $secure, $http_only);
		$this->_deleted_cookies[$name] = $name;
		if (isset($this->_cookies[$name])) {
			unset($this->_cookies[$name]);
		}

		return $success;
	}

	/**
	 * Метод получает значение cookie и удаляет её
	 * @param string $name      название
	 * @param string $path      путь
	 * @param string $domain    домен
	 * @param bool   $secure    только HTTPS
	 * @param bool   $http_only только HTTP
	 * @return mixed
	 */
	public function getAndDelete($name, $path = "/", $domain = "", $secure = false, $http_only = false) {
		$value = $this->get($name);
		$this->delete($name, $path, $domain, $secure, $http_only);

		return $value;
	}
}