<?php

declare(strict_types = 1);
const ORCHID = __DIR__ . '/../src/';

/**
 * Step 1: Require the Orchid Framework
 */
spl_autoload_register(function ($class) {
    $class_path = ORCHID . str_replace(['\\', '_'], '/', $class) . '.php';
    $class_path = str_replace('AEngine/', '', $class_path);

    if (file_exists($class_path)) {
        require_once($class_path);

        return;
    }
});

/**
 * Step 2: Instantiate a Orchid application
 *
 * This example instantiates a Orchid application using
 * its default settings.
 */
$app = AEngine\Orchid\App::getInstance();
$router = $app->router();

/**
 * Step 3: Define the Orchid application routes
 */
$router
    ->get('/', function ($request, $response, $args) {
        return $response->getBody()->write('Welcome to Orchid!');
    });

/**
 * Step 4: Run the Orchid application
 */
$app->run();
