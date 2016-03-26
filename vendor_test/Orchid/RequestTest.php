<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		Request::initialize("http://domain.ru/foo/bar?baz=quux", "GET");
	}

	public function testClientIp() {
		$this->assertNotFalse(filter_var(Request::getClientIp(), FILTER_VALIDATE_IP));
	}

	public function testClientLang() {
		$this->assertEquals("ru", Request::getClientLang("us"));
	}

	/**
	 * @param $app
	 * @param $path
	 * @param $expected
	 * @param $forApp
	 * @dataProvider providerSiteUrl
	 */
	public function testSiteUrl($app, $path, $expected, $forApp) {
		App::set("app", $forApp);
		$this->assertEquals($expected, Request::getUrl($app, $path));
	}

	public function providerSiteUrl() {
		return [
			["",		false,	"http://private.domain.ru",			"private"],
			["",		true,	"http://private.domain.ru/foo/bar",	"private"],
			["public",	false,	"http://domain.ru",					"private"],
			["public",	true,	"http://domain.ru/foo/bar",			"private"],
			["private",	false,	"http://private.domain.ru",			"private"],
			["private",	true,	"http://private.domain.ru/foo/bar",	"private"],
			["",		false,	"http://domain.ru",					"public"],
			["",		true,	"http://domain.ru/foo/bar",			"public"],
			["public",	false,	"http://domain.ru",					"public"],
			["public",	true,	"http://domain.ru/foo/bar",			"public"],
			["private",	false,	"http://private.domain.ru",			"public"],
			["private",	true,	"http://private.domain.ru/foo/bar",	"public"],
		];
	}

	public function testSitePath() {
		$this->assertEquals("/foo/bar", Request::getPath());
	}
}
