<?php

use Orchid\App;
use function Orchid\fetch_from_array;

class Session {
	/**
	 * Создание новой сессии с заданным именем
	 * @param null $sessionName
	 */
	public static function create($sessionName = null) {
		if (!strlen(session_id())) {
			$name = $sessionName ? $sessionName : App::get("session");

			session_name($name);
			session_start();
		}
	}

	/**
	 * Запись данных в текущую сессию
	 * @param string $key ключевое слово
	 * @param string $value значение для записи
	 */
	public static function write($key, $value) {
		$_SESSION[$key] = $value;
	}

	/**
	 * Чтение данных из текущей сессии по заданному ключу
	 * @param string $key ключевое слово
	 * @param string $default значение для записи
	 * @return Mixed
	 */
	public static function read($key, $default = null) {
		return fetch_from_array($_SESSION, $key, $default);
	}

	/**
	 * Удаление данных из текущей сессии по заданному ключу
	 * @param string $key ключевое слово
	 */
	public static function delete($key) {
		unset($_SESSION[$key]);
	}

	/**
	 * Уничтожение текущей сессии
	 */
	public static function destroy() {
		session_destroy();
	}
}
