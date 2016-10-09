<?php

spl_autoload_register(function ($class) {
    $class_path = __DIR__ . '/src/' . str_replace(['\\', '_'], '/', $class) . '.php';
    $class_path = str_replace('AEngine/Orchid/', '', $class_path);

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

$app = AEngine\Orchid\App::getInstance();

$app->router()->bind('/', function () use ($app) {
    return 'Hello World';
});

$app->run();
