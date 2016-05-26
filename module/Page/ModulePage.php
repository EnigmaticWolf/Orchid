<?php

namespace Page;

use Orchid\App;
use Orchid\Entity\Module;

/**
 * Class ModulePage has only static methods
 * 
 * @package Page
 */
class ModulePage extends Module {
	/**
	 * Initialize module
	 *
	 * @param App $app
	 */
	public static function initialize(App $app) {
		$app->router()
			->get("/*", "\\Page\\Controller\\Main");
	}
}