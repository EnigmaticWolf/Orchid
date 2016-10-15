<?php

namespace Psr\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface MiddlewareInterface
{
    /**
     * Process a request and return a response.
     *
     * Takes the incoming request and optionally modifies it before delegating
     * to the next handler to get a response. May modify the response before
     * ultimately returning it.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param callable          $next delegate function that will dispatch the next middleware component:
     *                                function (RequestInterface $request, ResponseInterface $response):
     *                                ResponseInterface
     *
     * @return ResponseInterface
     */
    public function __invoke(
        RequestInterface $request,
        ResponseInterface $response,
        callable $next
    );
}
