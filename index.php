<?php

declare(strict_types = 1);

require_once 'src/Orchid/App.php';
require_once 'src/Orchid/Event.php';
require_once 'src/Orchid/Request.php';
require_once 'src/Orchid/Response.php';
require_once 'src/Orchid/Router.php';

$app = \Orchid\App::getInstance();

$app->router()->bind('/', function () use ($app) {
    return 'Hello World';
});

$app->run();
