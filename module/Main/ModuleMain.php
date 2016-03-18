<?php

use Main\Controller\Main;
use Orchid\Entity\Module;
use Orchid\Router;

class ModuleMain extends Module {
	public static function initialize() {
		Router::get("/", function(){
			return Main::index();
		});
	}

	public static function HelloWorld() {
		return L::get("greeting") . " :)\n\r";
	}
}