<?php

namespace AEngine\Orchid\Interfaces;

interface RouteGroupInterface
{
    public function getCallable();

    public function setCallable($callable);

    public function getPattern();

    public function addMiddleware($callable);
}
