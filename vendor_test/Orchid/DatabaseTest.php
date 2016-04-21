<?php

namespace Orchid;

use PDO;
use PHPUnit_Framework_TestCase;

class DatabaseTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		Database::initialize([
			[
				"dsn"      => "sqlite:" . ORCHID . "/test.db",
				"username" => null,
				"password" => null,
				"options"  => [
					PDO::ATTR_PERSISTENT => true,
				],
			],
		]);
	}

	public function testCreateTable() {
		Database::query("
			CREATE TABLE 'key_value' (
				'id'	INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
				'key'	TEXT(100) NOT NULL,
				'value'	TEXT NOT NULL,
				'date'	TEXT(19)
			)
		");

		$stm = Database::query("SELECT `name` FROM `sqlite_master` WHERE `type` = 'table' AND `name` = 'key_value'");
		$this->assertEquals("key_value", $stm->fetchColumn());
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider providerKeyValues
	 */
	public function testInsert($key, $value) {
		Database::query("INSERT INTO `key_value` ('key', 'value') VALUES (?, ?)", [$key, $value]);
		$this->assertGreaterThan(0, Database::lastInsertId());
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider providerKeyValues
	 */
	public function testSelect($key, $value) {
		$stm = Database::query("SELECT `value` FROM `key_value` WHERE `key` = ?", [$key]);
		$this->assertEquals($value, $stm->fetchColumn());
	}

	/**
	 * @param $key
	 *
	 * @dataProvider providerKeyValues
	 */
	public function testDelete($key) {
		$stm = Database::query("DELETE FROM `key_value` WHERE `key` = ?", [$key]);
		$this->assertTrue(!!$stm->rowCount());
	}

	public function providerKeyValues() {
		return [
			["string", "foobar"],
			["int", 2010],
			["float", 20.10],
			["bool_1", true],
			["bool_2", false],
		];
	}

	public function testDeleteTable() {
		$stm = Database::query("DROP TABLE `key_value`;");
		$this->assertTrue(!!$stm->rowCount());

		// удалить файл вконце
		if (file_exists(ORCHID . "/test.db")) {
			unlink(ORCHID . "/test.db");
		}
	}
}