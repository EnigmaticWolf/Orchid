<?php

declare(strict_types = 1);
const ORCHID = __DIR__;

@ob_start('ob_gzhandler');
@ob_implicit_flush(0);

spl_autoload_register(function ($class) {
    $class_path = ORCHID . '/src/' . str_replace(['\\', '_'], '/', $class) . '.php';
    $class_path = str_replace('AEngine/', '', $class_path);

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

$app = AEngine\Orchid\App::getInstance();

$app->router()
    ->get('/', function () {
        return 'Hello World';
    });

// trigger before route event
//$app->event()->trigger('before');

// route and set response content
$app->response()->setContent($app->router()->dispatch());

// trigger after route event
//$app->event()->trigger('after');
