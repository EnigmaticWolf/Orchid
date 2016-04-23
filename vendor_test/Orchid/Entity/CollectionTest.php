<?php

namespace Orchid\Entity;

use PHPUnit_Framework_TestCase;

class CollectionTest extends PHPUnit_Framework_TestCase {
	/**
	 * @var CarList
	 */
	protected $collection = null;

	public function setUp() {
		$this->collection = new CarList([
			[
				"mark"   => "BMW",
				"model"  => "X1",
				"color"  => "Orange",
				"engine" => "20d",
				"year"   => 2015,
			],
			[
				"mark"   => "BMW",
				"model"  => "X5",
				"color"  => "",
				"engine" => "40d",
				"year"   => 2014,
			],
			[
				"mark"   => "Audi",
				"model"  => "Q3",
				"color"  => "White",
				"engine" => "2.0 TDI",
				"year"   => 2014,
			],
			[
				"mark"   => "Audi",
				"model"  => "Q5",
				"color"  => "",
				"engine" => "2.0 TFSI",
				"year"   => 2016,
			],
		]);
	}

	public function testIterator() {
		$data = [
			["engine" => "20d",],
			["engine" => "40d",],
			["engine" => "2.0 TDI",],
			["engine" => "2.0 TFSI",],
		];

		foreach ($this->collection as $key => $val) {
			$this->assertEquals($data[$key]["engine"], $val["engine"]);
		}
	}

	public function testCollect() {
		$this->assertEquals(["BMW", "BMW", "Audi", "Audi"], $this->collection->collect("mark"));
		$this->assertEquals(
			[
				['mark' => "BMW", 'model' => "X1"],
				['mark' => "BMW", 'model' => "X5"],
				['mark' => "Audi", 'model' => "Q3"],
				['mark' => "Audi", 'model' => "Q5"],
			],
			$this->collection->collect(["mark", "model"])
		);
		$this->assertEquals(["BMW" => "X5", "Audi" => "Q5"], $this->collection->collect("mark", "model"));
		$this->assertEquals(
			[
				2015 => ['mark' => "BMW", 'model' => "X1"],
				2014 => ['mark' => "Audi", 'model' => "Q3"],
				2016 => ['mark' => "Audi", 'model' => "Q5"],
			],
			$this->collection->collect("year", ["mark", "model"])
		);
	}

	public function testFind() {
		$this->assertEquals(
			[
				[
					"mark"   => "BMW",
					"model"  => "X1",
					"color"  => "Orange",
					"engine" => "20d",
					"year"   => 2015,
				],
				[
					"mark"   => "Audi",
					"model"  => "Q3",
					"color"  => "White",
					"engine" => "2.0 TDI",
					"year"   => 2014,
				],
			],
			$this->collection->find("color")->toArray()
		);
		$this->assertEquals(
			[
				[
					"mark"   => "BMW",
					"model"  => "X1",
					"color"  => "Orange",
					"engine" => "20d",
					"year"   => 2015,
				],
			],
			$this->collection->find("color", "Orange")->toArray()
		);
	}

	public function testSort() {
		$this->assertEquals([2014, 2014, 2015, 2016], $this->collection->sort("year")->collect("year"));
	}

	public function testSortCallable() {
		$this->assertEquals([2016, 2015, 2014, 2014], $this->collection->sort(function ($a, $b, $args) {
			return strnatcmp($b[$args], $a[$args]);
		}, "year")->collect("year"));
	}
}

class CarList extends Collection {}