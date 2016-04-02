<?php

use Orchid\App;
use Orchid\Extension\Str;
use Orchid\Registry;
use Orchid\Response;
use Orchid\Task;

// подключаем файл начальной загрузки
require_once("bootstrap.php");

// добавляем задачу выводящую время и память в заголовках ответа
Task::add("shutdown", function () {
	if (Registry::get("debug")) {
		Response::setHeaderCacheControl(284, 0, 0);
		Response::setHeaderExpires(DateTime::createFromFormat(DATE_RFC2822, "Sat, 01 Jan 00 00:00:00 +0000"));
		Response::setHeader("X-Time", round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 7) . "ms");
		Response::setHeader("X-Memory", Str::convertSize(memory_get_usage()));
	}
});

// запускаем приложение
App::run();
