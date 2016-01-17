<?php

namespace Orchid;

use PDO;
use PDOException;
use PDOStatement;

class Database {
	protected static $connection = [
		"master" => [],
		"slave"  => [],
	];

	/**
	 * @var PDO
	 */
	protected static $lastConnect = null;

	/**
	 * Инициализиует подключения
	 * @param array  $configs
	 */
	public static function initialize(array $configs) {
		$default = [
			"dsn"      => "",
			"username" => "",
			"password" => "",
			"option"   => [],
			"role"     => "master",
		];

		foreach ($configs as $index => $config) {
			$config = array_merge($default, $config);
			$key    = "db:" . $index;

			App::addService($key, function () use ($config) {
				try {
					return new PDO(
						$config["dsn"],
						$config["username"],
						$config["password"],
						$config["option"]
					);
				} catch (PDOException $e) {
					http_response_code(500);

					App::terminate("Подключение не удалось: " . $e->getMessage());
				}

				return null;
			});

			static::$connection[$config["role"] == "master" ? "master" : "slave"][] = $key;
		}
	}

	/**
	 * Возвращает объект PDO
	 * @param bool   $use_master
	 * @return null|PDO
	 */
	public static function getConnection($use_master = false) {
		$pool = [];

		$role = $use_master ? "master" : "slave";

		switch (true) {
			case !empty(static::$connection[$role]): {
				$pool = static::$connection[$role];
				break;
			}
			case !empty(static::$connection["master"]): {
				$pool = static::$connection["master"];
				break;
			}
			case !empty(static::$connection["slave"]): {
				$pool = static::$connection["slave"];
				break;
			}
		}

		if ($pool && $key = $pool[array_rand($pool)]) {
			return App::get($key);
		}

		return null;
	}

	/**
	 * Подготавливает и выполняет запрос к базе данных
	 * @param string     $query
	 * @param array      $params
	 * @param bool|false $use_master
	 * @return PDOStatement
	 */
	public static function query($query, array $params = [], $use_master = false) {
		// достаём соединение
		static::$lastConnect = static::getConnection(!$use_master ? !!strncmp($query, "SELECT", 6) : true);

		$stm = static::$lastConnect->prepare($query);
		$stm->execute($params);

		return $stm;
	}

	/**
	 * Возвращает ID последней вставленной строки
	 * @return string
	 */
	public static function lastInsertId() {
		return static::$lastConnect->lastInsertId();
	}
}