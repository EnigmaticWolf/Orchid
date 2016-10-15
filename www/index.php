<?php

declare(strict_types = 1);
const ORCHID = __DIR__ . "/..";

spl_autoload_register(function ($class) {
    $class_path = ORCHID . '/src/' . str_replace(['\\', '_'], '/', $class) . '.php';
    $class_path = str_replace('AEngine/', '', $class_path);

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

$app = AEngine\Orchid\App::getInstance();

$app->run();
