<?php

namespace Orchid\Extension;

use L;
use Orchid\App;
use PHPUnit_Framework_TestCase;

class i18nTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		App::path("lang", App::get("base_dir") . "/storage/i18n");
	}

	/**
	 * @param $code
	 * @param $expected
	 * @dataProvider providerLang
	 */
	public function testLang($code, $expected) {
		i18n::$force = $code;
		i18n::initialize();

		$this->assertEquals($expected, L::get("lang"));
	}

	public function providerLang() {
		return [
			["ru", "russian"],
			["en", "english"],
		];
	}
}
