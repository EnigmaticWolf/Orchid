<?php

namespace Orchid\Extension;

use PHPUnit_Framework_TestCase;

class StrTest extends PHPUnit_Framework_TestCase {
	public function testStart() {
		$this->assertTrue(Str::start("fo", "foobar"));
		$this->assertFalse(Str::start("bar", "foobar"));
	}

	public function testEnd() {
		$this->assertTrue(Str::end("ar", "foobar"));
		$this->assertFalse(Str::end("foo", "foobar"));
	}

	public function testTruncate() {
		$this->assertEquals("Take a step...", Str::truncate("Take a step and the road will appear by itself.", 15));
	}

	/**
	 * @param $count
	 * @param $expected
	 *
	 * @dataProvider providerEos
	 */
	public function testEos($count, $expected) {
		$this->assertEquals($expected, Str::eos($count, "яблоко", "яблока", "яблок"));
	}

	public function providerEos() {
		return [
			[1, "яблоко"],
			[2, "яблока"],
			[3, "яблока"],
			[4, "яблока"],
			[5, "яблок"],
		];
	}

	public function testEscape() {
		$this->assertEquals(
			"&lt;script src=&quot;somelink/js/jquery.min.js&quot;&gt;",
			Str::escape("<script src=\"somelink/js/jquery.min.js\">")
		);
	}

	public function testUnEscape() {
		$this->assertEquals(
			"<script src=\"somelink/js/jquery.min.js\">",
			Str::unEscape("&lt;script src=&quot;somelink/js/jquery.min.js&quot;&gt;")
		);
	}

	public function testTranslate() {
		$this->assertEquals("Privet mir!", Str::translate("Привет мир!"));
		$this->assertEquals("Привет мир!", Str::translate("Privet mir!", true));
	}

	public function testConvertSize() {
		$this->assertEquals("1 b", Str::convertSize(1));
		$this->assertEquals("1 kb", Str::convertSize(1 * 1024));
		$this->assertEquals("1 mb", Str::convertSize(1 * 1024 * 1024));
		$this->assertEquals("1 gb", Str::convertSize(1 * 1024 * 1024 * 1024));
		$this->assertEquals("1 tb", Str::convertSize(1 * 1024 * 1024 * 1024 * 1024));
		$this->assertEquals("1 pb", Str::convertSize(1 * 1024 * 1024 * 1024 * 1024 * 1024));
	}
}