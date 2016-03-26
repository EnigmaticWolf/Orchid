<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;

class RequestTest extends PHPUnit_Framework_TestCase {
	public function testClientIp() {
		$this->assertNotFalse(filter_var(Request::getClientIp(), FILTER_VALIDATE_IP));
	}

	public function testClientLang() {
		$this->assertEquals("ru", Request::getClientLang("us"));
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
		$this->assertEquals($expected, Request::getSiteUrl($path, $app));
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
		$this->assertEquals("/foo/bar", Request::getSitePath());
	}
}
