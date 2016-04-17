<?php

use Orchid\App;

define("ORCHID", __DIR__);

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

// дополнительный загрузшик
spl_autoload_register(function ($class) {
	foreach (App::getAutoloadList() as $dir) {
		$class_path = $dir . "_test/" . str_replace(["\\", "_"], "/", $class) . ".php";

		if (file_exists($class_path)) {
			require_once($class_path);

			return;
		}
	}
});

