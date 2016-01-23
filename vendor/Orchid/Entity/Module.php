<?php

namespace Orchid\Entity;

abstract class Module extends AppAware {
	/**
	 * Функция инициализации модуля
	 * @return mixed
	 */
	public static function initialize() {}
}
