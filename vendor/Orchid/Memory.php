<?php

namespace Orchid;

use Orchid\Classes\Driver\Cache\Memcache;

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
	 * Внутреннее дублирующее хранилище
	 * @var array
	 */
	protected static $cache = [];

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
			"port"    => "",
			"timeout" => 10,
			"role"    => "master",
		];

		foreach ($configs as $index => $config) {
			$config = array_merge($default, $config);
			$driver = strtolower($config["driver"]);
			$key    = "memory:" . $index;

			switch ($driver) {
				case "memcache": {
					App::addService($key, function () use ($config) {
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
	protected static function getInstance($use_master = false) {
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

			return App::get($key);
		}

		return false;
	}

	/**
	 * Генерирует ключ и возвращает его
	 * @param $key
	 * @return string
	 */
	protected static function getKey($key) {
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
			$key = static::getKey($key);

			if (!isset(static::$cache[$key])) {
				static::$cache[$key] = static::getInstance(0)->get($key);

				if (static::$cache[$key] === false) {
					static::$cache[$key] = $default;
				}
			}

			return static::$cache[$key];
		}

		return $default;
	}

	/**
	 * Записывает значение для ключа во внешнее хранилище
	 * @param string $key
	 * @param mixed  $value
	 * @param int    $expire
	 * @return bool
	 */
	public static function set($key, $value, $expire = 0) {
		$key = static::getKey($key);

		if (isset(static::$cache[$key])) {
			unset(static::$cache[$key]);
		}

		return static::getInstance(1)->set($key, $value, $expire);
	}

	/**
	 * Удаляет указанный ключ из внешнего хранилища
	 * @param string $key
	 * @return bool
	 */
	public static function delete($key) {
		$key = static::getKey($key);

		if (isset(static::$cache[$key])) {
			unset(static::$cache[$key]);
		}

		return static::getInstance(1)->delete($key);
	}

	/**
	 * Удаляет все ключи из внешнего хранилища
	 * @return bool
	 */
	public function flush() {
		static::$cache = [];

		return static::getInstance(1)->flush();
	}
}