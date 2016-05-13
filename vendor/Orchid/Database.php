<?php

namespace Orchid;

use PDO;
use PDOException;
use PDOStatement;
use Orchid\Entity\Config;
use Orchid\Entity\Exception\DatabaseException;

class Database {
	protected static $connection = [
		"master" => [],
		"slave"  => [],
	];

	/**
	 * @var PDO
	 */
	protected static $lastConnection = null;

	/**
	 * Инициализиует подключения
	 *
	 * @param Config|array $configs
	 */
	public static function initialize($configs) {
		$default = [
			"dsn"      => "",
			"username" => "",
			"password" => "",
			"options"  => [],
			"role"     => "master",
		];

		foreach ($configs as $index => $config) {
			$key = "database:" . $index;
			$config = array_merge($default, $config);

			App::addClosure($key, function () use ($config) {
				try {
					return new PDO(
						$config["dsn"],
						$config["username"],
						$config["password"],
						$config["options"]
					);
				} catch (PDOException $ex) {
					throw new DatabaseException("Cоединение с сервером базы данных завершилась неудачно (" . $ex->getMessage() . ")", 0, $ex);
				}
			});

			static::$connection[$config["role"] == "master" ? "master" : "slave"][] = $key;
		}
	}

	/**
	 * Возвращает объект PDO
	 *
	 * @param bool $use_master
	 *
	 * @return PDO
	 * @throws DatabaseException
	 */
	public static function getInstance($use_master = false) {
		$pool = [];
		$role = $use_master ? "master" : "slave";

		switch (true) {
			case !empty(static::$connection[$role]): {
				$pool = static::$connection[$role];
				break;
			}
			case !empty(static::$connection["master"]): {
				$pool = static::$connection["master"];
				$role = "master";
				break;
			}
			case !empty(static::$connection["slave"]): {
				$pool = static::$connection["slave"];
				$role = "slave";
				break;
			}
		}

		if ($pool) {
			if (is_array($pool)) {
				return static::$connection[$role] = App::getClosure($pool[array_rand($pool)]);
			} else {
				return $pool;
			}
		}

		throw new DatabaseException("Не удалось установить подключение");
	}

	/**
	 * Подготавливает и выполняет запрос к базе данных
	 *
	 * @param string     $query
	 * @param array      $params
	 * @param bool|false $use_master
	 *
	 * @return PDOStatement
	 */
	public static function query($query, array $params = [], $use_master = false) {
		// получаем соединение
		static::$lastConnection = static::getInstance(!$use_master ? !!strncmp($query, "SELECT", 6) : true);

		$stm = static::$lastConnection->prepare($query);
		$stm->execute($params);

		return $stm;
	}

	/**
	 * Возвращает ID последней вставленной строки
	 * 
	 * @return string
	 */
	public static function lastInsertId() {
		return static::$lastConnection->lastInsertId();
	}
}