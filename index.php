<?php

// подключаем файл начальной загрузки
require_once("bootstrap.php");

use Orchid\App;
use Orchid\Extension\Str;
use Orchid\Task;

// добавляем задачу выводящую время и память в заголовках ответа
Task::add("shutdown", function () {
	if (App::retrieve("debug", false)) {
		header("X-Time: " . round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 7) . "ms");
		header("X-Memory: " . Str::convertSize(memory_get_usage()));
	}
});

// запускаем приложение
App::run();
