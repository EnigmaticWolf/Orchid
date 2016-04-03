<?php

namespace Orchid\Extension;

use Orchid\App;
use Orchid\Registry;
use PHPUnit_Framework_TestCase;

class FileSystemTest extends PHPUnit_Framework_TestCase {
	public function testLs() {
		$this->assertTrue(is_array(FileSystem::ls(Registry::get("base_dir"))));
	}

	public function testMkdir() {
		$this->assertFalse(App::path(Registry::get("base_dir") . "/testFolder"));
		$this->assertTrue(FileSystem::mkdir(Registry::get("base_dir") . "/testFolder"));
		$this->assertTrue(!!App::path(Registry::get("base_dir") . "/testFolder"));
	}

	public function testWrite() {
		$this->assertEquals(5, FileSystem::write(Registry::get("base_dir") . "/testFolder/test.txt", "hello"));
	}

	public function testRead() {
		$this->assertEquals("hello", FileSystem::read(Registry::get("base_dir") . "/testFolder/test.txt"));
	}

	public function testCopy() {
		$this->assertTrue(FileSystem::copy(
			Registry::get("base_dir") . "/testFolder/test.txt",
			Registry::get("base_dir") . "/testFolder/test2.txt"
		));

		$this->assertEquals("hello", FileSystem::read(Registry::get("base_dir") . "/testFolder/test2.txt"));
	}

	public function testRename() {
		$this->assertTrue(FileSystem::rename(
			Registry::get("base_dir") . "/testFolder/test2.txt",
			Registry::get("base_dir") . "/testFolder/othername.txt"
		));

		$this->assertEquals("hello", FileSystem::read(Registry::get("base_dir") . "/testFolder/othername.txt"));
	}

	public function testDelete() {
		$this->assertTrue(FileSystem::delete(Registry::get("base_dir") . "/testFolder/othername.txt"));
		$this->assertFalse(FileSystem::read(Registry::get("base_dir") . "/testFolder/othername.txt"));
	}

	public function testRmdir() {
		$this->assertTrue(!!App::path(Registry::get("base_dir") . "/testFolder"));
		$this->assertTrue(FileSystem::rmdir(Registry::get("base_dir") . "/testFolder"));
		$this->assertFalse(App::path(Registry::get("base_dir") . "/testFolder"));
	}
}
