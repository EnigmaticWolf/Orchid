<?php

namespace Orchid\Extension {

	use Orchid\App;
	use Orchid\Entity\Exception\FileNotFoundException;
	use Orchid\Request;

	class i18n {
		/**
		 * Префикс языкового файла
		 *
		 * @var string
		 */
		public static $prefix = "";

		/**
		 * Принудительный выбор языка
		 *
		 * @var string
		 */
		public static $force = null;

		/**
		 * Буфферное хранилище языкового файла
		 *
		 * @var array
		 */
		public static $locale = [];

		/**
		 * Инициализирует языковую систему
		 *
		 * @param string $default язык по-умолчанию
		 *
		 * @throws FileNotFoundException
		 */
		public static function initialize($default = "ru") {
			$lang = static::$force ? static::$force : Request::getClientLang($default);
			// дириктория языковых файлов по-умолчанию
			$path = App::getBaseDir() . "/storage/i18n/" . static::$prefix . trim($lang) . ".php";

			if (($file = App::getPath("lang:" . static::$prefix . $lang . ".php")) !== false) {
				$path = $file;
			}

			if (file_exists($path)) {
				$ext = pathinfo($path);

				switch ($ext["extension"]) {
					case "ini": {
						static::$locale = parse_ini_file($path, true);
						break;
					}
					case "php": {
						static::$locale = require_once $path;
						break;
					}
				}
			} else {
				if (static::$force != $default) {
					static::$force = $default;
					static::initialize();
				} else {
					throw new FileNotFoundException("Не удалось найти файл языка");
				}
			}
		}
	}
}

namespace {

	use Orchid\Extension\i18n;

	class L {
		/**
		 * Возвращает интернационализированный текст для указанного ключа
		 *
		 * @param $key
		 *
		 * @return mixed
		 */
		public static function get($key) {
			if (array_key_exists($key, i18n::$locale)) {
				return i18n::$locale[$key];
			}

			return null;
		}
	}
}