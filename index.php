<?php

use Orchid\Extension\Str;

$app = require_once("bootstrap.php");

// add tasks outputting time and memory in the response header
$app->event()->on("shutdown", function () use ($app) {
	if ($app->isDebug()) {
		$app->response()
		    ->setHeaderCacheControl(284, 0, 0)
		    ->setHeaderExpires(DateTime::createFromFormat(DATE_RFC2822, "Sat, 01 Jan 00 00:00:00 +0000"))
		    ->setHeader("X-Time", round(microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"], 7) . "ms")
		    ->setHeader("X-Memory", Str::convertSize(memory_get_usage()));
	}
});

// запускаем приложение
$app->run();
