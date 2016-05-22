<?php

namespace Orchid\Extension;

class Str {
	/**
	 * Return true if the string starts with the specified character
	 *
	 * @param string $needle
	 * @param string $haystack
	 *
	 * @return Boolean;
	 */
	public static function start($needle, $haystack) {
		return !strncmp($haystack, $needle, mb_strlen($needle));
	}

	/**
	 * Return true if the string ends with the specified character
	 *
	 * @param string $needle
	 * @param string $haystack
	 *
	 * @return Boolean;
	 */
	public static function end($needle, $haystack) {
		$length = mb_strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
	}

	/**
	 * It is safe to truncate to the specified value, without losing the last word
	 *
	 * @param   string $string
	 * @param   int    $length
	 * @param   string $append
	 *
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
	 * Slope of the word, depending on the number
	 *
	 * @param int    $count
	 * @param string $one
	 * @param string $two
	 * @param string $five
	 *
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
	 * Convert Arabic number to Roman
	 *
	 * @param $int
	 *
	 * @return string
	 */
	public static function romanNumber($int) {
		$romanNumerals = [
			"M"  => 1000,
			"CM" => 900,
			"D"  => 500,
			"CD" => 400,
			"C"  => 100,
			"XC" => 90,
			"L"  => 50,
			"XL" => 40,
			"X"  => 10,
			"IX" => 9,
			"V"  => 5,
			"IV" => 4,
			"I"  => 1,
		];

		$result = "";
		foreach ($romanNumerals as $roman => $number) {
			$result .= str_repeat($roman, intval($int / $number));
			$int = $int % $number;
		}

		return $result;
	}

	/**
	 * Escape a string or an array of strings
	 *
	 * @param string|array $input
	 *
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
	 * Remove the screening in a row or an array of strings
	 *
	 * @param string|array $input
	 *
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
	 * Transliterate a russian string
	 *
	 * @param string $input
	 * @param bool   $back
	 *
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
	 * Returns a string representation of the data size
	 *
	 * @param int $size
	 *
	 * @return string
	 */
	public static function convertSize($size) {
		$unit = ["b", "kb", "mb", "gb", "tb", "pb"];

		return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . " " . $unit[$i];
	}
}