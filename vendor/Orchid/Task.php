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
	 * @param  string        $name     имя задачи
	 * @param  Closure|array $callback функция или массив указывающий на метод
	 * @param  int           $priority приоритет задачи
	 */
	public static function add($name, $callback, $priority = 0) {
		if (!isset(static::$task[$name])) {
			static::$task[$name] = [];
		}

		static::$task[$name][] = ["callback" => $callback, "priority" => $priority];
	}

	/**
	 * Запускает выполнение задачи с возможностью передачи параметров
	 * @param  string $name   имя задачи
	 * @param  array  $params передаваемые параметры
	 */
	public static function trigger($name, $params = []) {
		if (!empty(static::$task[$name])) {
			$queue = new SplPriorityQueue();
			foreach (static::$task[$name] as $index => $action) {
				$queue->insert($index, $action["priority"]);
			}

			$queue->top();
			while ($queue->valid()) {
				$index = $queue->current();
				if (is_callable(static::$task[$name][$index]["callback"])) {
					call_user_func_array(static::$task[$name][$index]["callback"], $params);
				}
				$queue->next();
			}
		}
	}
}