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

define("ORCHID",      __DIR__);
define("ORCHID_SRC",  ORCHID . DIRECTORY_SEPARATOR . "src");
define("ORCHID_TEST", ORCHID . DIRECTORY_SEPARATOR . "test");

// PSR auto class loader
spl_autoload_register(function ($class) {
	$class      = str_replace("\\", "/", $class);
	$class_type = (!strstr($class, "/Test") ? ORCHID_SRC : ORCHID_TEST) . DIRECTORY_SEPARATOR;
	$class_path = $class_type . $class . ".php";

	if (file_exists($class_path)) {
		require_once($class_path);
	}
});

$app = new Engine\Orchid();

$app->loadModule([
	ORCHID_SRC . "/Module",
]);

$app->run();