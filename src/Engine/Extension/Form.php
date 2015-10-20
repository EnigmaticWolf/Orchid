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
	 * Сформировать поле
	 * @param string $type тип поля
	 * @param array $args [name, data]
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
	 * Сформировать поле выбора
	 * @param string $name имя поля
	 * @param array $option массив значений выбора
	 * @param array $data массив дополнительных атрибутов
	 * @return string
	 */
	public function select($name, array $option = [], array $data = []) {
		return $this->render(array_merge($data, ["name" => $name, "type" => "select", "option" => $option]));
	}

	protected function render(array $data = []) {
		$default = [
			"method"      => "post",
			"id"          => null,
			"class"       => [],
			"error"       => [],
			"style"       => null,
			"type"        => "text",
			"name"        => null,
			"placeholder" => null,
			"tabindex"    => null,
			"readonly"    => false,
			"disabled"    => false,
			"required"    => false,
			"autofocus"   => false,
		];
		$form = "";

		// определяем тип требуемой формы
		switch ($data["type"]) {
			case "textarea": {
				$attr = [
					"autocomplete" => null,
					"maxlength"    => null,
					"cols"         => null,
					"rows"         => null,
					"wrap"         => null,
				];
				$data = array_merge($default, $attr, $data);

				$form .= "<textarea " . $this->getAttr($data, ["value"]) . ">";
				$form .= isset($data["value"]) ? $data["value"] : "";
				$form .= "</textarea>";

				break;
			}
			case "select": {
				$attr = [
					"option"   => [],
					"selected" => null,
					"multiple" => false,
				];
				$data = array_merge($default, $attr, $data);

				$form .= "<select  " . $this->getAttr($data) . ">";
				foreach ($data["option"] as $key => $val) {
					$form .= "<option";
					$form .= ' value="' . $key . '"';

					if ($data["selected"] && $data["selected"] == $key) {
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
						$attr = [
							"checked" => false,
							"value"   => null,
						];

						break;
					}
					case "file": {
						$attr = [
							"accept" => null,
							"value"  => null,
						];

						break;
					}
					default: {
						$attr = [
							"value" => null,
						];

						break;
					}
				}
				$data = array_merge($default, $attr, $data);
				$form .= "<input " . $this->getAttr($data) . " />";

				break;
			}
		}

		return $form;
	}

	/**
	 * Вспомогательный метод для генерации аттрибутов и свойств
	 * @param array $data
	 * @param array $exclude
	 * @return string
	 */
	protected function getAttr(array &$data = [], array $exclude = []) {
		$attr = "";

		if ($data["error"]) {
			$data["class"][] = "error";
		}
		if ($data["class"]) {
			$data["class"] = implode(" ", $data["class"]);
		}

		$exclude = array_merge($exclude, ["method", "option", "selected", "error"]);

		foreach ($data as $key => $val) {
			if (in_array($key, $exclude) || is_array($val)) {
				continue;
			}

			if (is_bool($val) && $val) {
				$attr .= " " . $key;
			} elseif (!is_bool($val) && !is_null($val)) {
				$attr .= " " . $key . "=\"" . $val . "\"";
			}
		}

		return $attr;
	}
}