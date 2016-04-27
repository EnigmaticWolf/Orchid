<?php

namespace Orchid\Extension;

class Session {
	/**
	 * Создаёт новую сессию с заданным именем
	 *
	 * @param string $name
	 */
	public static function create($name = "session") {
		if (!mb_strlen(session_id())) {
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
		if (array_key_exists($key, $_SESSION)) {
			return $_SESSION[$key];
		}

		return $default;
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
