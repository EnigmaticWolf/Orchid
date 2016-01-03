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

define("ORCHID", __DIR__);

// PSR auto class loader
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor/" . str_replace("\\", "/", $class) . ".php";
	if (file_exists($class_path)) {
		require_once($class_path);

		return;
	}
});

function pre(...$args) {
	echo "<pre>";
	foreach ($args as $key) {
		var_dump($key);
	}
	echo "</pre>";
}

use Orchid\App;
use Orchid\Database;
use Orchid\Task;

App::initialize();
Database::initialize([
	[
		"dsn"      => "mysql:dbname=omnisite;host=localhost",
		"user"     => "root",
		"password" => "53563256",
	],
	[
		"dsn"      => "mysql:dbname=omnisite;host=localhost",
		"user"     => "root",
		"password" => "53563256",
		"role"     => "slave",
	],
	[
		"dsn"      => "mysql:dbname=omnisite;host=localhost",
		"user"     => "root",
		"password" => "53563256",
		"role"     => "slave",
	],
]);
App::loadModule([
	ORCHID . "/modules",
]);
Task::add("shutdown", function () {
	if (App::retrieve("debug", false)) {
		header("X-Time: " . round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 7) . "ms");
		header("X-Memory: " . round(memory_get_usage() / 1024 / 1024, 7) . "mb");
	}
});
App::run();

$stm = Database::query("SELECT * FROM `page` WHERE `url` = :url", [":url" => "test/test"]);
$result = $stm->fetchAll(PDO::FETCH_ASSOC);

pre($result);