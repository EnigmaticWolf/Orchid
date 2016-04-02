<?php

namespace Orchid\Extension;

use Orchid\App;
use Orchid\Registry;
use PHPUnit_Framework_TestCase;
use stdClass;

class CacheTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		App::path("cache", Registry::get("base_dir") . "/storage/cache");
	}

	/**
	 * @param $key
	 * @param $value
	 * @dataProvider providerWriteReadDelete
	 */
	public function testWrite($key, $value) {
		$this->assertNotFalse(Cache::write($key, $value));
	}

	/**
	 * @param $key
	 * @param $expected
	 * @dataProvider providerWriteReadDelete
	 */
	public function testRead($key, $expected) {
		$this->assertEquals($expected, Cache::read($key, false));
	}

	/**
	 * @param $key
	 * @dataProvider providerWriteReadDelete
	 */
	public function testDelete($key) {
		$this->assertTrue(Cache::delete($key));
	}

	public function providerWriteReadDelete() {
		return [
			["baz",						"bar"],
			["int",						2010],
			["float",					20.10],
			["bool_1",					true],
			["bool_2",					false],
			["null",					null],
			["array",					[1, 2, 3, 4]],
			["object",					new stdClass],
		];
	}

	public function testTimer() {
		$this->assertNotFalse(Cache::write("test_timer", "foobar", 1));
		sleep(2);

		$this->assertFalse(Cache::read("test_timer", false));
	}

	public function testFlush() {
		Cache::flush();
	}
}
