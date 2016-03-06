<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;
use stdClass;

class AppTest extends PHPUnit_Framework_TestCase {
	public function testInitialize() {
		App::initialize([
			"debug"     => true,
			"instance"  => ["public", "private"],
			"locale"    => ["ru", "en"],
			"base_host" => "domain.ru",
		]);
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
			["baz",						"bar"],
			["bar/baz",					"foo"],
			["foo/bar/baz",				"quux"],
			["quux/foo/bar/baz",		"bat"],
			["bat/quux/foo/bar/baz",	"xyzzy"],
			["int",						2010],
			["float",					20.10],
			["bool_1",					true],
			["bool_2",					false],
			["null",					null],
			["array",					[1, 2, 3, 4]],
			["object",					new stdClass],
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

	public function providerRetrieve() {
		return [
			[null,						null],
			[true,						true],
			[false,						false],
			[0,							0],
			[1,							1],
			["foo",						"foo"],
			[function(){return "bar";},	"bar"],
			[new stdClass,				new stdClass],
		];
	}

	public function testClientIp() {
		$this->assertNotFalse(filter_var(App::getClientIp(), FILTER_VALIDATE_IP));
	}

	public function testClientLang() {
		$this->assertEquals("ru", App::getClientLang("us"));
	}

	/**
	 * @param $path
	 * @param $app
	 * @param $expected
	 * @param $forApp
	 * @dataProvider providerSiteUrl
	 */
	public function testSiteUrl($path, $app, $expected, $forApp) {
		App::set("app", $forApp);
		$this->assertEquals($expected, App::getSiteUrl($path, $app));
	}

	public function providerSiteUrl() {
		return [
			[false,	"",			"http://private.domain.ru",			"private"],
			[true,	"",			"http://private.domain.ru/foo/bar",	"private"],
			[false,	"public",	"http://domain.ru",					"private"],
			[true,	"public",	"http://domain.ru/foo/bar",			"private"],
			[false,	"private",	"http://private.domain.ru",			"private"],
			[true,	"private",	"http://private.domain.ru/foo/bar",	"private"],
			[false,	"",			"http://domain.ru",					"public"],
			[true,	"",			"http://domain.ru/foo/bar",			"public"],
			[false,	"public",	"http://domain.ru",					"public"],
			[true,	"public",	"http://domain.ru/foo/bar",			"public"],
			[false,	"private",	"http://private.domain.ru",			"public"],
			[true,	"private",	"http://private.domain.ru/foo/bar",	"public"],
		];
	}

	public function testSitePath() {
		$this->assertEquals("/foo/bar", App::getSitePath());
	}
}