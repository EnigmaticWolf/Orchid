<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;
use stdClass;

class RegistryTest extends PHPUnit_Framework_TestCase {
	/**
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider provider
	 */
	public function testSet($key, $value) {
		$this->assertTrue(Registry::set($key, $value));
	}

	/**
	 * @param $key
	 *
	 * @dataProvider provider
	 */
	public function testHas($key) {
		$this->assertTrue(Registry::has($key));
	}

	/**
	 * @param $key
	 * @param $expected
	 *
	 * @dataProvider provider
	 */
	public function testGet($key, $expected) {
		$this->assertEquals($expected, Registry::get($key));
	}

	/**
	 * @param $key
	 * @param $value
	 *
	 * @dataProvider provider
	 */
	public function testAdd($key, $value) {
		$this->assertTrue(Registry::add($key, $value));
	}

	/**
	 * @param $key
	 *
	 * @dataProvider provider
	 */
	public function testDelete($key) {
		$this->assertTrue(Registry::delete($key));
		$this->assertFalse(Registry::has($key));
	}

	public function provider() {
		return [
			["str", "foobar"],
			["int", 2010],
			["float", 20.10],
			["bool_1", true],
			["bool_2", false],
			["null", null],
			["array", [1, 2, 3, 4]],
			["object", new stdClass],
		];
	}

	public function testClosure() {
		$this->assertTrue(Registry::addClosure("closure", function ($a = 0) {
			return $a * $a;
		}));

		$this->assertEquals(16, Registry::getClosure("closure", 4));
	}
}
