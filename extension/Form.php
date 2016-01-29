<?php

/**
 * @method static string text(string $name, array $options = [])
 * @method static string password(string $name, array $options = [])
 * @method static string textarea(string $name, array $options = [])
 * @method static string checkbox(string $name, array $options = [])
 * @method static string radio(string $name, array $options = [])
 * @method static string file(string $name, array $options = [])
 * @method static string submit(string $name, array $options = [])
 * @method static string reset(string $name, array $options = [])
 * @method static string button(string $name, array $options = [])
 * @method static string hidden(string $name, array $options = [])
 */
class Form {
	/**
	 * Массив поддерживаемых типов
	 * @var array
	 */
	protected static $type = [
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
	 * @param array  $args [name, data]
	 * @return $this|mixed|null|string
	 */
	public static function __callStatic($type, $args) {
		if (in_array($type, static::$type)) {
			if (count($args) == 2) {
				list($name, $data) = $args;
			} else {
				$name = reset($args);
				$data = [];
			}

			return static::render(array_merge($data, ["name" => $name, "type" => $type]));
		}

		return null;
	}

	/**
	 * Сформировать поле выбора
	 * @param string $name   имя поля
	 * @param array  $option массив значений выбора
	 * @param array  $data   массив дополнительных атрибутов
	 * @return string
	 */
	public static function select($name, array $option = [], array $data = []) {
		return static::render(array_merge($data, ["name" => $name, "type" => "select", "option" => $option]));
	}

	protected static function render(array $data = []) {
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
		$form    = "";

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

				$form .= "<textarea " . static::getAttr($data, ["value", "type"]) . ">";
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

				$form .= "<select  " . static::getAttr($data) . ">";
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
				$form .= "<input " . static::getAttr($data) . " />";

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
	protected static function getAttr(array &$data = [], array $exclude = []) {
		$attr = "";

		if ($data["error"]) {
			$data["class"][] = "error";
		}
		if ($data["class"]) {
			$data["class"] = implode(" ", (is_array($data["class"]) ? $data["class"] : [$data["class"]]));
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