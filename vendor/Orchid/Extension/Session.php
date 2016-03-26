<?php

namespace Orchid\Extension;

use Orchid\App;
use function Orchid\fetch_from_array;

class Session {
	/**
	 * Создаёт новую сессию с заданным именем
	 *
	 * @param string $name
	 */
	public static function create($name = "session") {
		if (!strlen(session_id())) {
			session_name($name);
			session_start();
		}
	}

	/**
	 * Записывает данные в текущую сессию
	 *
	 * @param string $key   ключевое слово
	 * @param string $value значение для записи
	 */
	public static function write($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Возвращает данные из текущей сессии по заданному ключу
	 *
	 * @param string $key     ключевое слово
	 * @param string $default значение для записи
	 *
	 * @return Mixed
	 */
	public static function read($key, $default = null) {
		return fetch_from_array($_SESSION, $key, $default);
	}

	/**
	 * Удаляет данные из текущей сессии по заданному ключу
	 *
	 * @param string $key ключевое слово
	 */
	public static function delete($key) {
		unset($_SESSION[$key]);
	}

	/**
	 * Уничтожает текущую сессию
	 */
	public static function destroy() {
		session_destroy();
	}
}
