<?php

namespace Main\Controller;

use ModuleMain;
use Orchid\Entity\Controller;

class Main extends Controller {
	public static function index() {
		$data = [
			"hello" => ModuleMain::HelloWorld(),
		];

		return static::render("Main:View/Main.php", $data);
	}
}