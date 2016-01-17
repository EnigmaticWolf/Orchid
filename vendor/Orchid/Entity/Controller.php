<?php

namespace Orchid\Entity;

use Orchid\App;

abstract class Controller extends AppAware {
	protected static function render($template, array $data = []) {
		return App::render($template, $data);
	}
}