<?php

use Main\Controller\Main;
use Orchid\Entity\Config;
use Orchid\Entity\Module;
use Orchid\Router;

class ModuleMain extends Module {
	/**
	 * @var Config
	 */
	static $config = null;

	public static function initialize() {
		static::$config = Config::fromFile("Main:ExampleConfig.php");

		Router::get("/", function(){
			return Main::index();
		});
	}

	public static function HelloWorld() {
		return L::get("greeting") . " :)\n\r";
	}
}