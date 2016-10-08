<?php

namespace AEngine\Orchid\Entity;

use AEngine\Orchid\App;

abstract class Module
{
    /**
     * Initialize module
     *
     * @param App $app
     */
    public static function initialize(App $app)
    {
        // init module code here
    }
}
