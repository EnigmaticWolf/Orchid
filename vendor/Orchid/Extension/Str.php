<?php

namespace Orchid\Extension;

class Str {
	/**
	 * Возвращает true если строчка начинается с указанного символа
	 * @param string $needle искомый символ
	 * @param string $haystack строка
	 * @return Boolean;
	 */
	public static function start($needle, $haystack) {
		return !strncmp($haystack, $needle, strlen($needle));
	}

	/**
	 * Возвращает true если строчка заканчивается указанным символом
	 * @param string $needle искомый символ
	 * @param string $haystack строка
	 * @return Boolean;
	 */
	public static function end($needle, $haystack) {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	/**
	 * Безопасно обрезать строку до указанного значения, без потери последнего слова
	 * @param   string $string строка
	 * @param   int $length необходимая длинна
	 * @param   string $append символы в конце строки
	 * @return  string
	 */
	public static function truncate($string, $length, $append = "...") {
		$ret = substr($string, 0, $length);
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
	 * Склоненить слово в зависимости от числа
	 * @param int $count количество
	 * @param string $one слово
	 * @param string $two слово
	 * @param string $five слово
	 * @return string
	 */
	public static function eos($count, $one, $two, $five) {
		if (substr($count, -1, 1) == "1" && substr($count, -2, 2) != "11") {
			return $one;
		} else {
			if (substr($count, -2, 1) != 1 && substr($count, -1, 1) > 1 && substr($count, -1, 1) < 5) {
				return $two;
			} else {
				return $five;
			}
		}
	}

	/**
	 * Заэкранировать строку или массив строк
	 * @param string|array $input входящая строка
	 * @return string;
	 */
	public static function escape($input) {
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[$key] = static::escape($value);
			}
		} else {
			$input = htmlspecialchars($input, ENT_QUOTES);
		}

		return $input;
	}

	/**
	 * Убирать экранирование в строке или массив строк
	 * @param string|array $input входящая строка
	 * @return string;
	 */
	public static function unEscape($input) {
		if (is_array($input)) {
			foreach ($input as $key => $value) {
				$input[$key] = static::unEscape($value);
			}
		} else {
			$input = htmlspecialchars_decode($input, ENT_QUOTES);
		}

		return $input;
	}

	/**
	 * Транслитерировать строку
	 * @param string $input
	 * @param bool $back
	 * @return mixed
	 */
	public static function translate($input, $back = false) {
		$russian = [
			"А", "Б", "В", "Г", "Д", "Е", "Ё", "Ж", "З", "И", "Й", "К", "Л", "М", "Н", "О", "П", "Р", "С", "Т", "У", "Ф",
			"Х", "Ц", "Ч", "Ш", "Щ", "Ъ", "Ы", "Ь", "Э", "Ю", "Я", "а", "б", "в", "г", "д", "е", "ё", "ж", "з", "и", "й",
			"к", "л", "м", "н", "о", "п", "р", "с", "т", "у", "ф", "х", "ц", "ч", "ш", "щ", "ъ", "ы", "ь", "э", "ю", "я",
		];
		$latin = [
			"A", "B", "V", "G", "D", "E", "E", "Gh", "Z", "I", "Y", "K", "L", "M", "N", "O", "P", "R", "S", "T", "U", "F",
			"H", "C", "Ch", "Sh", "Sch", "Y", "Y", "Y", "E", "Yu", "Ya", "a", "b", "v", "g", "d", "e", "e", "gh", "z", "i",
			"y", "k", "l", "m", "n", "o", "p", "r", "s", "t", "u", "f", "h", "c", "ch", "sh", "sch", "y", "y", "y", "e", "yu",
			"ya",
		];

		return !$back ? str_replace($russian, $latin, $input) : str_replace($latin, $russian, $input);
	}

	/**
	 * Возвращает строковое представление размера данных
	 * @param int $size
	 * @return string
	 */
	public static function convertSize($size) {
		$unit = ["b", "kb", "mb", "gb", "tb", "pb"];

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . " " . $unit[$i];
	}
}