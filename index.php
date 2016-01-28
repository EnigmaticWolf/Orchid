<?php

define("ORCHID", __DIR__);

// PSR auto class loader
spl_autoload_register(function ($class) {
	$class_path = ORCHID . "/vendor/" . str_replace(["\\", "_"], "/", $class) . ".php";
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
use Orchid\Task;

App::initialize([
	"autoload" => [
		ORCHID . "/extension"
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