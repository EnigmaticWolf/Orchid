<?php

spl_autoload_register(function ($class) {
    $class_path = __DIR__ . '/src/' . str_replace(['\\', '_'], '/', $class) . '.php';

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

// function for debugging
function pre(...$args)
{
    echo '<pre>';
    foreach ($args as $obj) {
        var_dump($obj);
    }
    echo '</pre>';
}


$app = \Orchid\App::getInstance();

$app->router()->bind('/', function () use ($app) {
    return 'Hello World';
});

$app->run();
