<?php

use Orchid\App;
use Orchid\Classes\Module;
use Orchid\Router;

class ModuleMain extends Module {
	public static function initialize() {
		Router::get("/", function(){
			return App::invoke("Main\\Controller\\Main");
		});
	}

	public static function HelloWorld() {
		return "Здравствуй Мир! :) \n\r";
	}
}