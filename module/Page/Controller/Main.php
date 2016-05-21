<?php

namespace Page\Controller;

use Orchid\Entity\Controller;
use Orchid\Entity\View;

class Main extends Controller {
	public function index() {
		return View::fetch(
			$this->path("Page:View/Layout.php"),
			[
				"hello" => "Hello World!",
			]
		);
	}
}