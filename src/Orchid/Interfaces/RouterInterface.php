<?php

namespace AEngine\Orchid\Interfaces;

use InvalidArgumentException;
use Psr\Http\Message\ServerRequestInterface;
use RuntimeException;

/**
 * Router Interface
 */
interface RouterInterface
{
    /**
     * Add route
     *
     * @param string[] $methods Array of HTTP methods
     * @param string   $pattern The route pattern
     * @param callable $handler The route callable
     *
     * @return RouteInterface
     */
    public function map($methods, $pattern, $handler);

    /**
     * Dispatch router for HTTP request
     *
     * @param  ServerRequestInterface $request The current HTTP request object
     *
     * @return array
     *
     * @link   https://github.com/nikic/FastRoute/blob/master/src/Dispatcher.php
     */
    public function dispatch(ServerRequestInterface $request);

    /**
     * Add a route group to the array
     *
     * @param string   $pattern  The group pattern
     * @param callable $callable A group callable
     *
     * @return RouteGroupInterface
     */
    public function pushGroup($pattern, $callable);

    /**
     * Removes the last route group from the array
     *
     * @return bool True if successful, else False
     */
    public function popGroup();

    /**
     * Get named route object
     *
     * @param string $name Route name
     *
     * @return \AEngine\Orchid\Interfaces\RouteInterface
     *
     * @throws RuntimeException   If named route does not exist
     */
    public function getNamedRoute($name);

    /**
     * @param $identifier
     *
     * @return \AEngine\Orchid\Interfaces\RouteInterface
     */
    public function lookupRoute($identifier);

    /**
     * Build the path for a named route excluding the base path
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     *
     * @return string
     *
     * @throws RuntimeException         If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     */
    public function relativePathFor($name, array $data = [], array $queryParams = []);

    /**
     * Build the path for a named route including the base path
     *
     * @param string $name        Route name
     * @param array  $data        Named argument replacement data
     * @param array  $queryParams Optional query string parameters
     *
     * @return string
     *
     * @throws RuntimeException         If named route does not exist
     * @throws InvalidArgumentException If required data not provided
     */
    public function pathFor($name, array $data = [], array $queryParams = []);
}
