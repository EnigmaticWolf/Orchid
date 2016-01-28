<?php

namespace Main\Controller;

use ModuleMain;
use Orchid\App;
use Orchid\Entity\Controller;

class Main extends Controller {
	public static function index() {
		$data = [
			"hello" => ModuleMain::HelloWorld(),
		];

		return App::render("Main:View/Main.php", $data);
	}
}