<?php

namespace AEngine\Orchid\Http\Routing;

use AEngine\Orchid\Http\Request;
use AEngine\Orchid\Http\Response;

interface MiddlewareInterface
{
    /**
     * Running a query filter
     *
     * @param Request  $request
     * @param Response $response
     *
     * @return boolean if everything is as it should, return true
     */
    public static function __invoke(Request $request, Response $response);
}
