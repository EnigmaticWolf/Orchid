<?php

namespace AEngine\Orchid\Interfaces;

interface RouterInterface
{
    public function map(array $methods, $pattern, $callable, $priority = 0) : RouteInterface;
}
