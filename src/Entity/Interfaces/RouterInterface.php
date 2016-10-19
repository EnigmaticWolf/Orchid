<?php

namespace AEngine\Orchid\Entity\Interfaces;

use Psr\Http\Message\ServerRequestInterface;

interface RouterInterface
{
    public function get($pattern, $callable, $priority = 0);

    public function post($pattern, $callable, $priority = 0);

    public function put($pattern, $callable, $priority = 0);

    public function patch($pattern, $callable, $priority = 0);

    public function delete($pattern, $callable, $priority = 0);

    public function options($pattern, $callable, $priority = 0);

    public function any($pattern, $callable, $priority = 0);

    public function map(array $methods, $pattern, $callable, $priority = 0);

    public function getRoutes();

    public function group($pattern, $callable);

    public function dispatch(ServerRequestInterface $request);
}
