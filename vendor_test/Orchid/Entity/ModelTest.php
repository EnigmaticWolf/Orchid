<?php

namespace Orchid\Entity;

use PHPUnit_Framework_TestCase;

class ModelTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var Car
	 */
	protected $model = null;

	public function setUp() {
		$this->model = new Car([
			"mark"   => "BMW",
			"model"  => "X1",
			"color"  => "Orange",
			"engine" => "20d",
			"year"   => 2012,
		]);
	}

	public function testGet() {
		$this->assertEquals("BMW", $this->model->get("mark"));
		$this->assertEquals("X1", $this->model->get("model"));
		$this->assertEquals("Orange", $this->model->get("color"));
		$this->assertEquals("20d", $this->model->get("engine"));
		$this->assertEquals(2012, $this->model->get("year"));
	}

	public function testSetAndGet() {
		$this->assertTrue($this->model->exist("color"));

		$this->model->set("color", "White");
		$this->assertEquals("White", $this->model->get("color"));
	}

	public function testSetAndExist() {
		$this->model->set("someone", "foo");
		$this->assertFalse($this->model->exist("someone"));
	}

	public function testEmpty() {
		$this->assertFalse($this->model->isEmpty());
	}

	public function testDeleteAndGet() {
		$this->model->delete("color");

		$this->assertEmpty($this->model->get("color"));
	}

	public function testClear() {
		$this->model->clear();
		$this->assertTrue($this->model->isEmpty());
	}

	public function testToArray() {
		$array = [
			"mark"   => "BMW",
			"model"  => "X1",
			"color"  => "Orange",
			"engine" => "20d",
			"year"   => 2012,
		];

		$this->assertEquals($array, $this->model->toArray());
	}

	public function testCustom() {
		$this->assertEquals("BMW X1 (Orange)", $this->model->getMarkModel());
	}
}

class Car extends Model {
	protected static $default = [
		"mark"   => "",
		"model"  => "",
		"color"  => "",
		"engine" => "",
		"year"   => 0,
	];

	public function getMarkModel() {
		return $this->data["mark"] . " " . $this->data["model"] . " (" . $this->data["color"] . ")";
	}
}