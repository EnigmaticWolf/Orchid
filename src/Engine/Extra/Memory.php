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

namespace Engine\Extra;

use Memcache;

class Memory extends Memcache {
	protected $prefix;

	public function __construct($prefix = ""){
		$this->prefix = $prefix;
	}

	public function add($key, $val, $expire = null, &$flag = null) {
		return parent::add($this->prefix . ":" . $key, $val, $flag, $expire);
	}

	public function set($key, $val, $expire = null, &$flag = null) {
		return parent::set($this->prefix . ":" . $key, $val, $flag, $expire);
	}

	public function get($key, $default = false, &$flags = null) {
		$value = parent::get($this->prefix . ":" . $key, $flags);

		return $value ? $value : $default;
	}
}