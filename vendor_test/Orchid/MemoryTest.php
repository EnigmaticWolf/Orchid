<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;
use stdClass;

class MemoryTest extends PHPUnit_Framework_TestCase {
	public function testInitialize() {
		Memory::initialize([
			[
				"host" => "localhost",
				"port" => 11211,
			],
		]);
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider providerSetGetDelete
	 */
	public function testSet($key, $value) {
		$this->assertTrue(Memory::set($key, $value));
	}

	/**
	 * @param $key
	 * @param $expected
	 *
	 * @dataProvider providerSetGetDelete
	 */
	public function testGet($key, $expected) {
		$this->assertEquals($expected, Memory::get($key));
	}

	/**
	 * @param $key
	 *
	 * @dataProvider providerSetGetDelete
	 */
	public function testDelete($key) {
		$this->assertTrue(Memory::delete($key));
		$this->assertFalse(Memory::get($key));
	}

	public function providerSetGetDelete() {
		return [
			["string", "bar"],
			["int", 2010],
			["float", 20.10],
			["bool_1", true],
			["bool_2", false],
			["null", null],
			["array", [1, 2, 3, 4]],
			["object", new stdClass],
		];
	}

	public function testFlush() {
		$this->assertTrue(Memory::flush());
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider providerSetGetDelete
	 */
	public function testSetWithTag($key, $value) {
		$this->assertTrue(Memory::set($key, $value, 60, "test"));
	}

	public function testGetByTag() {
		$expected = [];
		foreach ($this->providerSetGetDelete() as $val) {
			$expected[$val[0]] = $val[1];
		}

		$this->assertEquals($expected, Memory::getByTag("test"));
	}

	public function testDeleteByTag() {
		$this->assertTrue(Memory::deleteByTag("test"));
		$this->assertFalse(Memory::deleteByTag("test"));
	}
}