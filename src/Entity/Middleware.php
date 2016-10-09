<?php

namespace AEngine\Orchid\Entity;

use AEngine\Orchid\Http\Request;
use AEngine\Orchid\Http\Response;

abstract class Middleware
{
    abstract public static function handle(Request $request, Response $response);
}
