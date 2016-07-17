<?php

use Orchid\App;

define("ORCHID", __DIR__);

// PSR-compatible bootloader key files
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor/" . str_replace(["\\", "_"], "/", $class) . ".php";

	if (file_exists($class_path)) {
		require_once($class_path);

		return;
	}
});

// function for debugging
function pre(...$args) {
	echo "<pre>";
	foreach ($args as $obj) {
		var_dump($obj);
	}
	echo "</pre>";
}

$app = App::getInstance([
	/* config params here */
]);

$app->loadModule([
	ORCHID . "/module",
]);
