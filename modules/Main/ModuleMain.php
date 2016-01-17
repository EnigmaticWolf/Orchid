<?php

use Orchid\Entity\Module;
use Orchid\Router;

class ModuleMain extends Module {
	public static function initialize() {
		Router::bindClass("Main\\Controller\\Main", "*");
	}

	public static function HelloWorld() {
		return "Здравствуй Мир! :) \n\r";
	}
}