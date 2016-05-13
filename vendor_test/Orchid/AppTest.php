<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;

class AppTest extends PHPUnit_Framework_TestCase {
	public static function setUpBeforeClass() {
		$_SERVER["DOCUMENT_ROOT"] = ORCHID;
	}

	public function testInitialize() {
		App::initialize([
			"debug"     => true,
			"instance"  => ["public", "private"],
			"locale"    => ["ru", "en"],
			"base_host" => "domain.ru",
		]);
	}

	public function testLoadModule() {
		App::loadModule([
			ORCHID . "/module", // папка модулей
		]);

		$this->assertTrue(class_exists("ModuleMain"));
	}

	public function testPath() {
		App::addPath("test", __DIR__);

		$this->assertEquals(__FILE__, App::getPath("test:AppTest.php"));
	}

	public function testIsAbsolutePath() {
		$this->assertTrue(App::isAbsolutePath(__DIR__));
		$this->assertFalse(App::isAbsolutePath("../"));
	}

	public function testPathToUrl() {
		$this->assertEquals("/vendor_test/Orchid/AppTest.php", App::pathToUrl(__FILE__));
		$this->assertEquals("/vendor_test/Orchid/AppTest.php", App::pathToUrl("test:AppTest.php"));
	}

	public function testGetUrl() {
		$this->assertEquals("http://domain.ru/", App::getUrl("/"));
		$this->assertEquals("http://domain.ru/test", App::getUrl("/test"));

		$this->assertEquals("http://other.domain.ru/", App::getUrl("/", "other"));
		$this->assertEquals("http://other.domain.ru/test", App::getUrl("/test", "other"));
	}
}