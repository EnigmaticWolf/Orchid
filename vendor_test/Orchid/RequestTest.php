<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		$_SERVER["REMOTE_ADDR"] = "127.0.0.1";
		$_SERVER["HTTP_ACCEPT_LANGUAGE"] = "ru,en;q=0.8,en-US;q=0.6";
	}

	public function testInitialize(){
		Request::initialize("http://domain.ru/foo/bar?baz=quux", "GET");
	}

	public function testGetHeader() {
		$this->assertNotEmpty(Request::getHeader("Accept-Language"));
	}

	public function testGetClientIp() {
		$this->assertNotFalse(filter_var(Request::getClientIp(), FILTER_VALIDATE_IP));
	}

	public function testGetClientLang() {
		$this->assertEquals("ru", Request::getClientLang("us"));
	}

	/**
	 * @param $app
	 * @param $path
	 * @param $expected
	 * @param $forApp
	 *
	 * @dataProvider providerGetSiteUrl
	 */
	public function testGetSiteUrl($app, $path, $expected, $forApp) {
		Registry::set("app", $forApp);
		$this->assertEquals($expected, Request::getUrl($app, $path));
	}

	public function providerGetSiteUrl() {
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

	public function testGetSitePath() {
		$this->assertEquals("/foo/bar", Request::getPath());
	}
}
