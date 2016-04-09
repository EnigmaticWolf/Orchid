<?php

namespace Orchid\Extension;

use PHPUnit_Framework_TestCase;

class FormTest extends PHPUnit_Framework_TestCase {
	public function testText() {
		$this->assertEquals(
			'<input type="text" name="uname" />',
			Form::text("uname")
		);
	}

	public function testTextFilled() {
		$_POST["testUName"] = "Aleksey";

		$this->assertEquals(
			'<input type="text" name="testUName" value="Aleksey" />',
			Form::text("testUName")
		);
	}

	public function testTextWithId() {
		$this->assertEquals(
			'<input id="primary" type="text" name="uname" />',
			Form::text("uname", ["id" => "primary"])
		);
	}

	public function testTextWithClass() {
		$this->assertEquals(
			'<input class="color-red" type="text" name="uname" />',
			Form::text("uname", ["class" => ["color-red"]])
		);
	}

	public function testTextWithError() {
		$this->assertEquals(
			'<input class="error" type="text" name="uname" />',
			Form::text("uname", ["error" => "Cannot be empty"])
		);
	}

	public function testTextWithClassAndError() {
		$this->assertEquals(
			'<input class="color-red error" type="text" name="uname" />',
			Form::text("uname", ["class" => ["color-red"], "error" => "Cannot be empty"])
		);
	}

	public function testTextWithStyle() {
		$this->assertEquals(
			'<input style="color: red;" type="text" name="uname" />',
			Form::text("uname", ["style" => "color: red;"])
		);
	}

	public function testTextWithDataAttrs() {
		$this->assertEquals(
			'<input type="text" name="uname" data-text="some text" />',
			Form::text("uname", ["data" => ["text" => "some text"]])
		);
	}

	public function testTextWithPlaceholder() {
		$this->assertEquals(
			'<input type="text" name="uname" placeholder="Type you name" />',
			Form::text("uname", ["placeholder" => "Type you name"])
		);
	}

	public function testTextWithTabindex() {
		$this->assertEquals(
			'<input type="text" name="uname" tabindex="1" />',
			Form::text("uname", ["tabindex" => 1])
		);
	}

	public function testTextReadonly() {
		$this->assertEquals(
			'<input type="text" name="uname" readonly />',
			Form::text("uname", ["readonly" => true])
		);
	}

	public function testTextDisabled() {
		$this->assertEquals(
			'<input type="text" name="uname" disabled />',
			Form::text("uname", ["disabled" => true])
		);
	}

	public function testTextRequired() {
		$this->assertEquals(
			'<input type="text" name="uname" required />',
			Form::text("uname", ["required" => true])
		);
	}

	public function testTextAutofocus() {
		$this->assertEquals(
			'<input type="text" name="uname" autofocus />',
			Form::text("uname", ["autofocus" => true])
		);
	}

	public function testSearch() {
		$this->assertEquals(
			'<input type="search" name="google" />',
			Form::search("google")
		);
	}

	public function testUrl() {
		$this->assertEquals(
			'<input type="url" name="url" />',
			Form::url("url")
		);
	}

	public function testEmail() {
		$this->assertEquals(
			'<input type="email" name="email" />',
			Form::email("email")
		);
	}

	public function testTel() {
		$this->assertEquals(
			'<input type="tel" name="phone" />',
			Form::tel("phone")
		);
	}

	public function testPassword() {
		$this->assertEquals(
			'<input type="password" name="pass" />',
			Form::password("pass")
		);
	}

	public function testNumber() {
		$this->assertEquals(
			'<input type="number" name="count" />',
			Form::number("count")
		);
	}

	public function testRange() {
		$this->assertEquals(
			'<input type="range" name="count" max="10" min="0" step="1" />',
			Form::range("count", ["min" => 0, "max" => 10, "step" => 1])
		);
	}

	public function testTime() {
		$this->assertEquals(
			'<input type="time" name="time" />',
			Form::time("time")
		);
	}

	public function testDate() {
		$this->assertEquals(
			'<input type="date" name="date" max="1991-09-30" min="1991-09-01" />',
			Form::date("date", ["min" => "1991-09-01", "max" => "1991-09-30"])
		);
	}

	public function testDatetime() {
		$this->assertEquals(
			'<input type="datetime-local" name="datetime" max="1991-09-30" min="1991-09-01" step="2" />',
			Form::datetime("datetime", ["min" => "1991-09-01", "max" => "1991-09-30", "step" => "2"])
		);
	}

	public function testWeek() {
		$this->assertEquals(
			'<input type="week" name="week" max="1991-W20" min="1991-W1" />',
			Form::week("week", ["min" => "1991-W1", "max" => "1991-W20"])
		);
	}

	public function testMonth() {
		$this->assertEquals(
			'<input type="month" name="month" max="1991-12" min="1991-1" />',
			Form::month("month", ["min" => "1991-1", "max" => "1991-12"])
		);
	}

	public function testColor() {
		$this->assertEquals(
			'<input type="color" name="color" />',
			Form::color("color")
		);
	}

	public function testTextarea() {
		$this->assertEquals(
			'<textarea name="textarea">hello world</textarea>',
			Form::textarea("textarea", ["value" => "hello world"])
		);
	}

	public function testCheckbox() {
		$this->assertEquals(
			'<input type="checkbox" name="checkbox" />',
			Form::checkbox("checkbox")
		);
	}

	public function testRadio() {
		$this->assertEquals(
			'<input type="radio" name="radio" value="Lviv" checked />',
			Form::radio("radio", ["value" => "Lviv", "checked" => true])
		);
	}

	public function testRadioChecked() {
		$_GET["radioTest"] = 2;

		$this->assertEquals(
			'<input type="radio" name="radioTest" value="1" />',
			Form::radio("radioTest", ["value" => 1, "method" => "get"])
		);
		$this->assertEquals(
			'<input type="radio" name="radioTest" value="2" checked />',
			Form::radio("radioTest", ["value" => 2, "method" => "get"])
		);
	}

	public function testSubmit() {
		$this->assertEquals(
			'<input type="submit" name="submit" />',
			Form::submit("submit")
		);
	}

	public function testReset() {
		$this->assertEquals(
			'<input type="reset" name="reset" />',
			Form::reset("reset")
		);
	}

	public function testButton() {
		$this->assertEquals(
			'<input type="button" name="button" />',
			Form::button("button")
		);
	}

	public function testFile() {
		$this->assertEquals(
			'<input type="file" name="file" accept="image/*" />',
			Form::file("file", ["accept" => "image/*"])
		);
	}

	public function testHidden() {
		$this->assertEquals(
			'<input type="hidden" name="hidden" />',
			Form::hidden("hidden")
		);
	}
}
