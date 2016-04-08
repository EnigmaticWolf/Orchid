<?php

namespace Main\Controller;

use ModuleMain;
use Orchid\Entity\View;

class Main {
	public static function index() {
		$data = [
			"hello" => ModuleMain::HelloWorld(),
		];

		return View::fetch("Main:View/Main.php", $data);
	}
}