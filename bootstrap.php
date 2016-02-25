<?php

define("ORCHID", __DIR__);

// PSR совместимый загрузщик файлов
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor/" . str_replace(["\\", "_"], "/", $class) . ".php";

	if (file_exists($class_path)) {
		require_once($class_path);

		return;
	}
});

// функция для отладки в Debug
function pre(...$args) {
	echo "<pre>";
	foreach ($args as $obj) {
		var_dump($obj);
	}
	echo "</pre>";
}

use Orchid\App;

// инициализация приложения
App::initialize();

// подключение модулей
App::loadModule([
	ORCHID . "/module",
]);