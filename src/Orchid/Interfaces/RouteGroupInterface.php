<?php

namespace AEngine\Orchid\Interfaces;

interface RouteGroupInterface
{
    public function __construct($pattern, $callable);

    public function getCallable();

    public function setCallable($callable);

    public function getPattern();

    public function addMiddleware($callable);

    public function __invoke();
}
