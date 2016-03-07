<?php

namespace Orchid\Extension;

use PHPUnit_Framework_TestCase;

class CryptaTest extends PHPUnit_Framework_TestCase {
	public function testEncryptAndDecrypt() {
		$encrypted = Crypta::encrypt("foobar");
		$this->assertEquals("foobar", Crypta::decrypt($encrypted));
	}

	public function testHashAndCheck() {
		$hash = Crypta::hash("foobar");
		$this->assertTrue(Crypta::check("foobar", $hash));
	}
}