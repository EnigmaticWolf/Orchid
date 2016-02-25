<?php

namespace Main\Controller;

use ModuleMain;
use Orchid\App;

class Main {
	public static function index() {
		$data = [
			"hello" => ModuleMain::HelloWorld(),
		];

		return App::render("Main:View/Main.php", $data);
	}
}