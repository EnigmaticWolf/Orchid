<?php

namespace Orchid\Extension;

use Orchid\App;

class Crypta {
	/**
	 * Зашифровать переданную строку
	 *
	 * @param string $input строка которая будет зашифрованна
	 *
	 * @return string зашифрованная строка
	 */
	public static function encrypt($input) {
		return base64_encode(static::crypt($input));
	}

	/**
	 * Расшифровать переданную строку
	 *
	 * @param string $input строка которая будет расшифрованна
	 *
	 * @return string расшифрованная строка
	 */
	public static function decrypt($input) {
		return static::crypt(base64_decode($input));
	}

	/**
	 * Вспомогательный метод для работы со строкой строку
	 *
	 * @param string $input строка
	 *
	 * @return string обработанная строка
	 */
	protected static function crypt($input) {
		$salt = md5(App::getSecret());
		$len = mb_strlen($input);
		$gamma = "";
		$n = $len > 100 ? 8 : 2;
		while (mb_strlen($gamma) < $len) {
			$gamma .= substr(pack("H*", sha1($gamma . $salt)), 0, $n);
		}

		return $input ^ $gamma;
	}

	/**
	 * Сгенерировать хешсумму для строки
	 *
	 * @param string $string строка из которой получить хешсумму
	 *
	 * @return string хешсумма 140 символов
	 */
	public static function hash($string) {
		$salt = substr(hash("whirlpool", uniqid(rand() . App::getSecret(), true)), 0, 12);
		$hash = hash("whirlpool", $salt . $string);
		$saltPos = (mb_strlen($string) >= mb_strlen($hash) ? mb_strlen($hash) : mb_strlen($string));

		return substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
	}

	/**
	 * Проверить строку на соответствие хешсумме
	 *
	 * @param string $string     проаеряемая строка
	 * @param string $hashString хешсумма
	 *
	 * @return Boolean
	 */
	public static function check($string, $hashString) {
		$saltPos = (mb_strlen($string) >= mb_strlen($hashString) ? mb_strlen($hashString) : mb_strlen($string));
		$salt = substr($hashString, $saltPos, 12);
		$hash = hash("whirlpool", $salt . $string);

		return $hashString == substr($hash, 0, $saltPos) . $salt . substr($hash, $saltPos);
	}
}