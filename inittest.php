<?php

define("ORCHID", __DIR__);

// Параметры необходимые для тестов
$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
$_SERVER["REQUEST_METHOD"] = "GET";
$_SERVER["REQUEST_URI"] = "http://domain.ru/foo/bar?baz=quux";
$_SERVER["HTTP_ACCEPT_LANGUAGE"] = "ru,en;q=0.8,en-US;q=0.6";

// PSR совместимый загрузщик основных файлов
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor/" . str_replace(["\\", "_"], "/", $class) . ".php";

	if (file_exists($class_path)) {
		require_once($class_path);

		return;
	}
});

// PSR совместимый загрузщик тестовых файлов
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor_test/" . str_replace(["\\", "_"], "/", $class) . ".php";

	if (file_exists($class_path)) {
		require_once($class_path);

		return;
	}
});

use Orchid\App;

// дополнительный загрузшик
spl_autoload_register(function ($class) {
	foreach (App::get("autoload") as $dir) {
		$class_path = $dir . "_test/" . str_replace(["\\", "_"], "/", $class) . ".php";

		if (file_exists($class_path)) {
			require_once($class_path);

			return;
		}
	}
});

