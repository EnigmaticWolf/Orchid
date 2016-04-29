<?php

use Main\Controller\Main;
use Orchid\Entity\Configuration;
use Orchid\Entity\Module;
use Orchid\Router;

class ModuleMain extends Module {
	/**
	 * @var Configuration
	 */
	static $config = null;

	public static function initialize() {
		static::$config = Configuration::fromFile("Main:ExampleConfig.php");

		Router::get("/", function(){
			return Main::index();
		});
	}

	public static function HelloWorld() {
		return L::get("greeting") . " :)\n\r";
	}
}