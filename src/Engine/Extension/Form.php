<?php

/*
 * Copyright (c) 2011-2014 AEngine
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished
 * to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace Engine\Extension;

use Engine\Extension;
use function Engine\pre;

class Form extends Extension {
	/**
	 * Массив поддерживаемых типов
	 * @var array
	 */
	protected $type = [
		"text",
		"password",
		"textarea",
		"checkbox",
		"radio",
		"file",
		"select",
		"submit",
		"reset",
		"button",
		"hidden",
	];

	/**
	 * Метод для формирования поля ввода
	 * @param string $type тип поля ввода
	 * @param array  $args [name, data]
	 * @return $this|mixed|null|string
	 */
	public function __call($type, $args) {
		if (in_array($type, $this->type)) {
			if (count($args) == 2) {
				list($name, $data) = $args;
			} else {
				$name = reset($args);
				$data = [];
			}

			return $this->render(array_merge($data, ["name" => $name, "type" => $type]));
		}

		return null;
	}

	/**
	 * Метод формирует поле выбора
	 * @param string $name   имя поля
	 * @param array  $option массив значений выбора
	 * @param array  $data   массив дополнительных атрибутов
	 * @return string
	 */
	public function select($name, array $option = [], array $data = []) {
		return $this->render(array_merge($data, ["name" => $name, "type" => "select", "option" => $option]));
	}

	protected function render(array $data = []) {
		$default = [
			"method"      => "post",
			"id"          => "",
			"class"       => [],
			"error"       => [],
			"style"       => "",
			"type"        => "text",
			"name"        => "",
			"placeholder" => "",
			"tabindex"    => "",
			"readonly"    => false,
			"disabled"    => false,
			"required"    => false,
			"autofocus"   => false,
		];
		$data    = array_merge($default, $data);
		$form    = "";

		if ($data["error"]) {
			$data["class"][] = "error";
		}

		if ($data["class"]) {
			$data["class"] = implode(" ", $data["class"]);
		}

		// определяем тип требуемой формы
		switch ($data["type"]) {
			case "textarea": {
				$default = [
					"autocomplete" => "",
					"maxlength"    => 0,
					"cols"         => 0,
					"rows"         => 0,
					"wrap"         => 0,
				];
				$data    = array_merge($default, $data);

				$form .= "<textarea " . $this->getAttr($data) . ">";
				$form .= "</textarea>";

				break;
			}
			case "select": {
				$default = [
					"option"   => [],
					"selected" => "",
					"multiple" => false,
				];
				$data    = array_merge($default, $data);

				$form .= "<select  " . $this->getAttr($data) . ">";
				foreach ($data["option"] as $key => $val) {
					$option = false;
					if (is_array($val)) {
						list($val, $option) = $val;
					}

					$form .= "<option";
					$form .= ' value="' . $key . '"';
					if ($option) {
						$form .= $option;
					} elseif ($data["selected"] == $key) {
						$form .= " selected";
					}
					$form .= ">";
					$form .= $val;
					$form .= "</option>";
				}
				$form .= "</select>";

				break;
			}
			default: {
				// выделяем конкретно тип
				switch ($data["type"]) {
					case "radio":
					case "checkbox": {
						$default = [
							"checked" => false,
							"value"   => "",
						];
						$data    = array_merge($default, $data);

						break;
					}
					case "file": {
						$default = [
							"accept" => "",
							"value"  => "",
						];
						$data    = array_merge($default, $data);

						break;
					}
					default: {
						$default = [
							"value" => "",
						];
						$data    = array_merge($default, $data);

						break;
					}
				}

				$form .= "<input " . $this->getAttr($data) . " />";

				break;
			}
		}

		return $form;
	}

	/**
	 * Метод формирующий атрибуты для полей
	 * @param array $data
	 * @return string
	 */
	protected function getAttr(array &$data = []) {
		$attr = "";
		foreach ($data as $key => $val) {
			if (!empty($val) && !in_array($key, ["method", "option", "selected", "error"]) && !is_array($val)) {
				$attr .= " " . (is_bool($val) ? $key : $key . "=\"" . $val . "\"");
			}
		}

		return $attr;
	}
}