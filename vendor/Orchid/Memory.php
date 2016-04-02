<?php

namespace Orchid;

use Orchid\Entity\Driver\Cache\Memcache;

class Memory {
	/**
	 * Запрещает чтение из внешнего хранилища
	 * @var bool
	 */
	public static $disabled = false;

	/**
	 * Префикс ключей
	 * @var string
	 */
	public static $prefix = "";

	/**
	 * Список ключей которые будут сохраняться в буффер
	 * @var array
	 */
	public static $cachedKeys = [];

	/**
	 * Внутреннее хранилище
	 * @var array
	 */
	protected static $buffer = [];

	/**
	 * Массив объектов соединений с внешними хранилищами
	 * @var array
	 */
	protected static $instance = [];

	/**
	 * Инициализиует подключения
	 * @param array $configs
	 * @return void
	 */
	public static function initialize(array $configs) {
		$default = [
			"driver"  => "memcache",
			"host"    => "",
			"port"    => null,
			"timeout" => 10,
			"role"    => "master",
		];

		foreach ($configs as $index => $config) {
			$config = array_merge($default, $config);
			$driver = strtolower($config["driver"]);
			$key = "memory:" . $index;

			switch ($driver) {
				case "memcache": {
					Registry::addClosure($key, function () use ($config) {
						return new Memcache(
							$config["host"],
							$config["port"],
							$config["timeout"]
						);
					});

					break;
				}
			}

			static::$instance[strtolower($config["role"]) == "master" ? "master" : "slave"][] = $key;
		}
	}

	/**
	 * Открывает и возвращает соединение с внешним хранилищем
	 * @param bool $use_master
	 * @return Memcache|false
	 */
	public static function getInstance($use_master = false) {
		$pool = [];
		$role = $use_master ? "master" : "slave";

		switch (true) {
			case !empty(static::$instance[$role]): {
				$pool = static::$instance[$role];
				break;
			}
			case !empty(static::$instance["master"]): {
				$pool = static::$instance["master"];
				$role = "master";
				break;
			}
			case !empty(static::$instance["slave"]): {
				$pool = static::$instance["slave"];
				$role = "slave";
				break;
			}
		}

		if ($pool && $key = $pool[array_rand($pool)]) {
			static::$instance[$role] = [$key];

			return Registry::getClosure($key);
		}

		return false;
	}

	/**
	 * Генерирует ключ и возвращает его
	 * @param $key
	 * @return string
	 */
	public static function getKey($key) {
		return static::$prefix ? static::$prefix . ":" . $key : $key;
	}

	/**
	 * Производит чтение ключа из внешнего хранилища и возвращает значение
	 * @param string $key
	 * @param mixed  $default
	 * @return mixed
	 */
	public static function get($key, $default = false) {
		if (!static::$disabled) {
			if (isset(static::$buffer[$key])) {
				$value = static::$buffer[$key];
			} else {
				$value = static::getInstance(false)->get($key);

				foreach (static::$cachedKeys as $k) {
					if (strpos($key, $k) === 0) {
						static::$buffer[$key] = $value;
					}
				}
			}

			return $value !== false ? $value : $default;
		}

		return $default;
	}

	/**
	 * Записывает значение для ключа во внешнее хранилище
	 * @param string      $key
	 * @param mixed       $value
	 * @param int         $expire
	 * @param string|null $tag
	 * @return bool
	 */
	public static function set($key, $value, $expire = 0, $tag = null) {
		if (isset(static::$cachedKeys[$key])) {
			unset(static::$buffer[$key]);
		}

		return static::getInstance(true)->set($key, $value, $expire, $tag);
	}

	/**
	 * Удаляет указанный ключ из внешнего хранилища
	 * @param string $key
	 * @return bool
	 */
	public static function delete($key) {
		if (isset(static::$cachedKeys[$key])) {
			unset(static::$buffer[$key]);
		}

		return static::getInstance(true)->delete($key);
	}

	/**
	 * Удаляет все ключи из внешнего хранилища
	 * @return bool
	 */
	public static function flush() {
		static::$buffer = [];

		return static::getInstance(true)->flush();
	}

	/**
	 * Достаёт значения по указанному тегу
	 * @param string $tag
	 * @return array
	 */
	public static function getByTag($tag) {
		return static::getInstance(false)->getByTag($tag);
	}

	/**
	 * Удаляет значения по указанному тегу
	 * @param string $tag
	 * @return bool
	 */
	public static function deleteByTag($tag) {
		return static::getInstance(true)->deleteByTag($tag);
	}
}