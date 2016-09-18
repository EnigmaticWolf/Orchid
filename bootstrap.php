<?php

use Orchid\App;

const ORCHID = __DIR__;

// PSR-compatible boot loader key files
spl_autoload_register(function ($class) {
    $class_path = ORCHID . '/vendor/' . str_replace(['\\', '_'], '/', $class) . '.php';

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

$app = App::getInstance([
    /* config params here */
]);

$app->loadModule([
    ORCHID . '/module',
]);
