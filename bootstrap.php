<?php

define("ORCHID", __DIR__);

// PSR совместимый загрузщик основных файлов
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
use Orchid\Extension\i18n;

// инициализация приложения
App::initialize([
	"locale" => ["ru", "en"],
]);

// подключение модулей
App::loadModule([
	ORCHID . "/module",
]);

// принудительная смена языка
if (isset($_REQUEST["lang"])) {
	i18n::$force = $_REQUEST["lang"];
}

// инициализация расширения интернационализации
i18n::initialize();