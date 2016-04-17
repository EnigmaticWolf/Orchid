<?php

namespace Orchid\Extension;

use Orchid\App;
use PHPUnit_Framework_TestCase;

class FileSystemTest extends PHPUnit_Framework_TestCase {
	public function testLs() {
		$this->assertTrue(is_array(FileSystem::ls(App::getBaseDir())));
	}

	public function testMkdir() {
		$this->assertFalse(App::path(App::getBaseDir() . "/testFolder"));
		$this->assertTrue(FileSystem::mkdir(App::getBaseDir() . "/testFolder"));
		$this->assertTrue(!!App::path(App::getBaseDir() . "/testFolder"));
	}

	public function testWrite() {
		$this->assertEquals(5, FileSystem::write(App::getBaseDir() . "/testFolder/test.txt", "hello"));
	}

	public function testRead() {
		$this->assertEquals("hello", FileSystem::read(App::getBaseDir() . "/testFolder/test.txt"));
	}

	public function testCopy() {
		$this->assertTrue(FileSystem::copy(
			App::getBaseDir() . "/testFolder/test.txt",
			App::getBaseDir() . "/testFolder/test2.txt"
		));

		$this->assertEquals("hello", FileSystem::read(App::getBaseDir() . "/testFolder/test2.txt"));
	}

	public function testRename() {
		$this->assertTrue(FileSystem::rename(
			App::getBaseDir() . "/testFolder/test2.txt",
			App::getBaseDir() . "/testFolder/othername.txt"
		));

		$this->assertEquals("hello", FileSystem::read(App::getBaseDir() . "/testFolder/othername.txt"));
	}

	public function testDelete() {
		$this->assertTrue(FileSystem::delete(App::getBaseDir() . "/testFolder/othername.txt"));
		$this->assertFalse(FileSystem::read(App::getBaseDir() . "/testFolder/othername.txt"));
	}

	public function testRmdir() {
		$this->assertTrue(!!App::path(App::getBaseDir() . "/testFolder"));
		$this->assertTrue(FileSystem::rmdir(App::getBaseDir() . "/testFolder"));
		$this->assertFalse(App::path(App::getBaseDir() . "/testFolder"));
	}
}
