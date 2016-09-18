<?php

namespace Orchid\Entity;

use Orchid\App;

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
