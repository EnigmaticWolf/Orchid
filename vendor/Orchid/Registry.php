<?php

namespace Orchid;

use Closure;

class Registry {
	protected static $storage = [];

	/**
	 * Добавляет элемент в реестр
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public static function add($key, $value) {
		if (array_key_exists($key, static::$storage)) {
			if (is_array(static::$storage[$key])) {
				static::$storage[$key][] = $value;
			} else {
				static::$storage[$key] = [static::$storage[$key], $value];
			}

			return true;
		}

		return false;
	}

	/**
	 * Добавляет замыкание в реестр
	 *
	 * @param string  $name
	 * @param Closure $callable
	 *
	 * @return bool
	 */
	public static function addClosure($name, $callable) {
		return static::set($name, function ($param = null) use ($callable) {
			static $object;

			if ($object === null) {
				$object = $callable($param);
			}

			return $object;
		});
	}

	/**
	 * Задает элемент в реестре
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return bool
	 */
	public static function set($key, $value) {
		static::$storage[$key] = $value;

		return true;
	}

	/**
	 * Задаёт массив элементов
	 *
	 * @param array $data
	 *
	 * @return bool
	 */
	public static function setAll(array $data) {
		foreach ($data as $key => $value) {
			static::$storage[$key] = $value;
		}

		return true;
	}

	/**
	 * Проверяет наличие ключа в реестре
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function has($key) {
		return array_key_exists($key, static::$storage);
	}

	/**
	 * Возвращает элемент из реестра
	 *
	 * @param string $key
	 * @param null   $default значение по-умолчанию
	 *
	 * @return mixed
	 */
	public static function &get($key, $default = null) {
		if (array_key_exists($key, static::$storage)) {
			return static::$storage[$key];
		}

		return $default;
	}

	/**
	 * Возвращает результат работы замыкания
	 *
	 * @param string $name
	 * @param array  ...$param
	 *
	 * @return mixed
	 */
	public static function getClosure($name, ...$param) {
		if (array_key_exists($name, static::$storage) && is_callable(static::$storage[$name])) {
			return call_user_func_array(static::$storage[$name], $param);
		}

		return null;
	}

	/**
	 * Удаляет значение для указанного ключа
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public static function delete($key) {
		if (isset(static::$storage[$key])) {
			unset(static::$storage[$key]);

			return true;
		}

		return false;
	}
}
