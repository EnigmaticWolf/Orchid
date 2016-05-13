<?php

namespace Orchid;

use Closure;

class Event {
	/**
	 * Хранилище созданных обработчиков
	 * @var array
	 */
	protected static $event = [];

	/**
	 * Добавляет обработчик события
	 *
	 * @param  string        $name     имя события
	 * @param  Closure|array $callback функция или массив указывающий на метод
	 * @param  int           $priority приоритет
	 */
	public static function add($name, $callback, $priority = 0) {
		static::$event[$name][] = ["callback" => $callback, "priority" => $priority];
	}

	/**
	 * Запускает выполнение события с возможностью передачи параметров
	 *
	 * @param  string $name   имя события
	 * @param  array  $params передаваемые параметры
	 */
	public static function trigger($name, $params = []) {
		if (!empty(static::$event[$name])) {
			arsort(static::$event[$name], SORT_NUMERIC);

			foreach (static::$event[$name] as $ev) {
				if (is_callable($ev["callback"])) {
					call_user_func_array($ev["callback"], $params);
				}
			}
		}
	}
}