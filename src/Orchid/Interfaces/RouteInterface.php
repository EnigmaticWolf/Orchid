<?php

namespace AEngine\Orchid\Interfaces;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouteInterface
{
    public function getCallable();

    public function setCallable($callable);

    public function getMethods();

    public function getGroups();

    public function getPriority();

    public function getIdentifier();

    public function setArgument($name, $value);

    public function setArguments(array $arguments);

    public function getArgument($name = '', $default = null);

    public function addMiddleware($callable);

    public function callMiddlewareStack(ServerRequestInterface $req, ResponseInterface $res);
}
