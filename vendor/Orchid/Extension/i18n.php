<?php

namespace Orchid\Extension {

	use Orchid\App;
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
		 */
		public static function initialize($default = "ru") {
			$lang = static::$force ? static::$force : Request::getClientLang($default);
			$file = static::getLangFilePath(trim($lang));

			if (file_exists($file)) {
				static::$locale = require_once($file);
			} else {
				if (static::$force != $default) {
					static::$force = $default;
					static::initialize();
				} else {
					http_response_code(500);

					App::terminate("Не удалось найти языковый файл");
				}
			}
		}

		/**
		 * Возвращает путь и название языкового файла
		 *
		 * @param string $locale выбранный язык
		 *
		 * @return string
		 */
		protected static function getLangFilePath($locale) {
			// директориия хранилища по-умолчанию
			$path = App::getBaseDir() . "/storage/i18n/";

			if (($lang = App::path("lang:")) !== false) {
				$path = $lang;
			}

			return $path . static::$prefix . $locale . ".php";
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