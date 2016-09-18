<?php

namespace Orchid\Entity;

use Orchid\App;

abstract class Controller
{
    /**
     * If set to False stops controller execution
     *
     * @var bool
     */
    public $execute = true;
    /**
     * @var App
     */
    protected $app;

    /**
     * Controller constructor
     *
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * Alias for App method
     *
     * @param $path
     *
     * @return string
     */
    public function path($path)
    {
        return $this->app->path($path, false);
    }
}
