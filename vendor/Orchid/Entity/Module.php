<?php

namespace Orchid\Entity;

use Orchid\App;

abstract class Module {
	/**
	 * Initialize module
	 *
	 * @param App $app
	 */
	public static function initialize($app) {
		// init code here
	}
}