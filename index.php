<?php

spl_autoload_register(function ($class) {
    $class_path = __DIR__ . '/src/' . str_replace(['\\', '_'], '/', $class) . '.php';

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

$app = \Orchid\App::getInstance();

$app->router()->bind('/', function () use ($app) {
    return 'Hello World';
});

$app->run();