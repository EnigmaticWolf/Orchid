<?php

namespace Page;

use Orchid\Entity\Module;

class ModulePage extends Module {
	/**
	 * Initialize module
	 *
	 * @param App $app
	 */
	public static function initialize($app) {
		$app->router()
			->get("/*", "\\Page\\Controller\\Main");
	}
}