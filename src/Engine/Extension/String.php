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

class String extends Extension {
	/**
	 * Метод возвращает True если строчка начинается с символа
	 * @param String $needle   искомый символ
	 * @param String $haystack строка
	 * @return Boolean;
	 */
	public function start($needle, $haystack) {
		return !strncmp($haystack, $needle, strlen($needle));
	}

	/**
	 * Метод возвращает True если строчка заканчивается символом
	 * @param String $needle   искомый символ
	 * @param String $haystack строка
	 * @return Boolean;
	 */
	public function end($needle, $haystack) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	/**
	 * Метод безопасно обрезает строку до указанного значения, без потери последнего слова
	 * @param   String  $string строка
	 * @param   integer $length необходимая длинна
	 * @param   String  $append символы в конце строки
	 * @return  String
	 */
	public function truncate($string, $length, $append = "...") {
		$ret        = substr($string, 0, $length);
		$last_space = strrpos($ret, " ");
		if ($last_space !== false && $string != $ret) {
			$ret = substr($ret, 0, $last_space);
		}
		if ($ret != $string) {
			$ret .= $append;
		}

		return $ret;
	}

	/**
	 * Метод для склонения слов
	 * @param integer $count  количество
	 * @param String  $single слово
	 * @param String  $double слово
	 * @param String  $triple слово
	 * @return string
	 */
	public function eos($count, $single, $double, $triple) {
		if (substr($count, -1, 1) == "1" && substr($count, -2, 2) != "11") {
			return $single;
		} else {
			if (substr($count, -2, 1) != 1 && substr($count, -1, 1) > 1 && substr($count, -1, 1) < 5) {
				return $double;
			} else {
				return $triple;
			}
		}
	}

	/**
	 * Метод экранирует строку или массив строк
	 * @param String|Array $input входящая строка
	 * @return String;
	 */
	public function escape($input) {
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[$key] = $this->escape($value);
			}
		} else {
			$input = htmlspecialchars($input, ENT_QUOTES);
		}

		return $input;
	}

	/**
	 * Метод убирает экранирование в строке или массив строк
	 * @param String|Array $input входящая строка
	 * @return String;
	 */
	public function unEscape($input) {
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[$key] = $this->unEscape($value);
			}
		} else {
			$input = htmlspecialchars_decode($input, ENT_QUOTES);
		}

		return $input;
	}

	/**
	 * Метод транслятор
	 * @param String $input
	 * @param bool   $back
	 * @return mixed
	 */
	function translate($input, $back = false) {
		$russian = array(
			"А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф",
			"Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я", "а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й",
			"к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я",
			" ",
		);
		$latin = array(
			"A", "B", "V", "G", "D", "E", "E", "Gh", "Z", "I", "Y", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F",
			"H", "C", "Ch", "Sh", "Sch", "Y", "Y", "Y", "E", "Yu", "Ya", "a", "b", "v", "g", "d", "e", "e", "gh", "z", "i",
			"y", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "ch", "sh", "sch", "y", "y", "y", "e", "yu",
			"ya", "-",
		);

		return !$back ? str_replace($russian, $latin, $input) : str_replace($latin, $russian, $input);
	}
}