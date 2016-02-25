<?php

// подключаем файл начальной загрузки
require_once("bootstrap.php");

use Orchid\App;

// PSR загрузщик тестовых файлов
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor_test/" . str_replace(["\\", "_"], "/", $class) . ".php";

	if (file_exists($class_path)) {
		require_once($class_path);

		return;
	}
});

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

