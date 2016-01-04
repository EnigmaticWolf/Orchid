<?php

/*
 * Copyright (c) 2011-2016 AEngine
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

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
		$name = strtolower($name);
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