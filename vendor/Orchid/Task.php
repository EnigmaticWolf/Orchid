<?php

namespace Orchid;

use Closure;
use SplPriorityQueue;

class Task {
	/**
	 * Хранилище созданных задач
	 * @var array
	 */
	protected static $task = [];

	/**
	 * Добавляет задачу
	 *
	 * @param  string        $name     имя задачи
	 * @param  Closure|array $callback функция или массив указывающий на метод
	 * @param  int           $priority приоритет задачи
	 */
	public static function add($name, $callback, $priority = 0) {
		static::$task[$name][] = ["callback" => $callback, "priority" => $priority];
	}

	/**
	 * Запускает выполнение задачи с возможностью передачи параметров
	 *
	 * @param  string $name   имя задачи
	 * @param  array  $params передаваемые параметры
	 */
	public static function trigger($name, $params = []) {
		if (!empty(static::$task[$name])) {
			arsort(static::$task[$name], SORT_NUMERIC);

			foreach (static::$task[$name] as $task) {
				if (is_callable($task["callback"])) {
					call_user_func_array($task["callback"], $params);
				}
			}
		}
	}
}