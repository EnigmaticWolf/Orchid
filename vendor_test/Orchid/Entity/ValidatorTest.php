<?php

namespace Orchid\Entity;

use Orchid\Entity\Validate\Base;
use Orchid\Entity\Validate\Str;
use Orchid\Entity\Validate\Type;
use PHPUnit_Framework_TestCase;

class ValidatorTest extends PHPUnit_Framework_TestCase {
	public function testBaseValidators() {
		$time = time();
		$data = [
			"date_0"      => date('d.m.Y h:i:s', $time),
			"date_1"      => date('d.m.Y h:i:s', $time),
			"date_2"      => date('d.m.Y h:i:s', $time),
			"date_3"      => date('d.m.Y h:i:s', $time),
			"min_int"     => 10,
			"min_str"     => "Hello world",
			"min_str_utf" => "Привет мир",
			"max_int"     => 10,
			"max_str"     => "Hello world",
			"max_str_utf" => "Привет мир",
		];

		$valid = new Valid($data);
		$result = $valid
					->attr("date_0")
						->addRule($valid->date($valid::$DATE_RU))
					->attr("date_1")
						->addRule($valid->date($valid::$DATE_EN))
					->attr("date_2")
						->addRule($valid->date($valid::$DATE_US))
					->attr("date_3")
						->addRule($valid->date($valid::$DATE_DB))
					->attr("min_int")
						->addRule($valid->min(5))
					->attr("min_str")
						->addRule($valid->min(5))
					->attr("min_str_utf")
						->addRule($valid->min(5))
					->attr("max_int")
						->addRule($valid->max(15))
					->attr("max_str")
						->addRule($valid->max(15))
					->attr("max_str_utf")
						->addRule($valid->max(15))
					->validate();

		$this->assertTrue(!is_array($result));
		$this->assertEquals(date($valid::$DATE_RU, $time), $data["date_0"]);
		$this->assertEquals(date($valid::$DATE_EN, $time), $data["date_1"]);
		$this->assertEquals(date($valid::$DATE_US, $time), $data["date_2"]);
		$this->assertEquals(date($valid::$DATE_DB, $time), $data["date_3"]);
	}

	public function testStrValidators() {
		$data = [
			"bad_str" => "'\"><`\\",
			"email"   => "foo@bar.com",
			"ip"      => "127.0.0.1",
		];

		$valid = new Valid($data);
		$result = $valid
					->attr("bad_str")
						->addRule($valid->escape())
					->attr("email")
						->addRule($valid->isEmail())
					->attr("ip")
						->addRule($valid->isIp())
					->validate();

		$this->assertTrue(!is_array($result));
		$this->assertEquals("&#039;&#34;&#62;&#60;&#96;&#92;", $data["bad_str"]);
	}

	public function testTypeValidators() {
		$data = [
			"empty"    => "",
			"notEmpty" => "foo",
			"bool"     => true,
			"int"      => 120,
			"str"      => "foo",
			"toBool"   => 1,
			"toInt"    => "12",
			"toDouble" => "12.17446732",
			"toStr"    => 10,
		];

		$valid = new Valid($data);
		$result = $valid
					->attr("empty")
						->addRule($valid->isEmpty())
					->attr("notEmpty")
						->addRule($valid->isNotEmpty())
					->attr("bool")
						->addRule($valid->isBoolean())
					->attr("int")
						->addRule($valid->isNumeric())
					->attr("str")
						->addRule($valid->isString())
					->attr("toBool")
						->addRule($valid->toBoolean())
					->attr("toInt")
						->addRule($valid->toInteger())
					->attr("toDouble")
						->addRule($valid->toDouble(2))
					->attr("toStr")
						->addRule($valid->toString())
					->validate();

		$this->assertTrue(!is_array($result));
		$this->assertEquals(true, $data["toBool"]);
		$this->assertEquals(12, $data["toInt"]);
		$this->assertEquals(12.17, $data["toDouble"]);
		$this->assertEquals("10", $data["toStr"]);
	}
}

class Valid extends Validator {
	use Base, Str, Type;
}