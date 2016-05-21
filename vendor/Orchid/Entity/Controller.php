<?php

namespace Orchid\Entity;

use Orchid\App;

abstract class Controller {
	/**
	 * @var App
	 */
	protected $app;

	/**
	 * If set to False stops controller execution
	 *
	 * @var boolean
	 */
	public $execute = true;

	/**
	 * Controller constructor
	 *
	 * @param App $app
	 */
	public function __construct(App $app) {
		$this->app = $app;
	}

	/**
	 * Alias for App method
	 *
	 * @param $path
	 *
	 * @return string
	 */
	public function path($path) {
		return $this->app->path($path, false);
	}
}