<?php

require_once 'vendor/autoload.php';

$app = \Orchid\App::getInstance();

$app->router()->bind('/', function () use ($app) {
    return 'Hello World';
});

$app->run();
