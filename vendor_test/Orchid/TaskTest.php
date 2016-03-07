<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;

class TaskTest extends PHPUnit_Framework_TestCase {
	public static $result = null;

	public function testAdd() {
		Task::add("task", function(){
			TaskTest::$result = "passed";
		});
	}

	public function testTrigger() {
		Task::trigger("task");

		$this->assertEquals("passed", static::$result);
	}

	public function testAddPriority() {
		Task::add("taskPriority", function(){
			TaskTest::$result .= "p-10";
		}, 10);
		Task::add("taskPriority", function(){
			TaskTest::$result .= "p-20";
		}, 20);
	}

	public function testTriggerPriority() {
		TaskTest::$result = "";
		Task::trigger("taskPriority");

		$this->assertEquals("p-20p-10", static::$result);
	}
}