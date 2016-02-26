<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;
use stdClass;

class AppTest extends PHPUnit_Framework_TestCase {
	public function testInitialize() {
		$this->assertTrue(App::initialize());
	}

	/**
	 * @param $key
	 * @param $value
	 * @dataProvider providerSetGetDelete
	 */
	public function testSet($key, $value) {
		$this->assertTrue(App::set($key, $value));
	}

	/**
	 * @param $key
	 * @param $expected
	 * @dataProvider providerSetGetDelete
	 */
	public function testGet($key, $expected) {
		$this->assertEquals($expected, App::get($key));
	}

	/**
	 * @param $key
	 * @dataProvider providerSetGetDelete
	 */
	public function testDelete($key) {
		$this->assertTrue(App::delete($key));
		$this->assertNull(App::get($key));
	}

	public function providerSetGetDelete() {
		return [
			["baz", "bar"],
			["bar/baz", "foo"],
			["foo/bar/baz", "quux"],
			["quux/foo/bar/baz", "bat"],
			["bat/quux/foo/bar/baz", "xyzzy"],
			["int", 2010],
			["float", 20.10],
			["bool_1", true],
			["bool_2", false],
			["null", null],
			["array", [1, 2, 3, 4]],
			["object", new stdClass],
		];
	}

	/**
	 * @param $default
	 * @param $expected
	 * @dataProvider providerRetrieve
	 */
	public function testRetrieve_1($default, $expected) {
		$this->assertEquals($expected, App::retrieve("unknown", $default));
	}

	/**
	 * @param $value
	 * @param $expected
	 * @dataProvider providerRetrieve
	 */
	public function testRetrieve_2($value, $expected) {
		App::set("known", $value);
		$this->assertEquals($expected, App::retrieve("known"));
	}

	public function providerRetrieve () {
		return [
			[null, null],
			[true, true],
			[false, false],
			[0, 0],
			[1, 1],
			["foo", "foo"],
			[function(){return "bar";}, "bar"],
			[new stdClass, new stdClass],
		];
	}
}