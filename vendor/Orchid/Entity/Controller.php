<?php

namespace Orchid\Entity;

abstract class Controller extends AppAware {
	public static function index() {
		return "Override index method";
	}
}