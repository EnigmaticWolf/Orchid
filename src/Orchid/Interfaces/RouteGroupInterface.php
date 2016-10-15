<?php

namespace AEngine\Orchid\Interfaces;

use AEngine\Orchid\App;

/**
 * RouteGroup Interface
 */
interface RouteGroupInterface
{
    /**
     * Get route pattern
     *
     * @return string
     */
    public function getPattern();

    /**
     * Prepend middleware to the group middleware collection
     *
     * @param callable|string $callable The callback routine
     *
     * @return RouteGroupInterface
     */
    public function add($callable);

    /**
     * Execute route group callable in the context of the Orchid App
     *
     * This method invokes the route group object's callable, collecting
     * nested route objects
     *
     * @param App $app
     */
    public function __invoke(App $app);
}
