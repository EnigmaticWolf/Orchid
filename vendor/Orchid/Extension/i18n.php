<?php

namespace Orchid\Extension {

	use Orchid\App;

	class i18n {
		/**
		 * Префикс языкового файла
		 * @var string
		 */
		public static $prefix = "";

		/**
		 * Принудительный выбор языка
		 * @var string|null
		 */
		public static $force = null;

		/**
		 * Буфферное хранилище языкового файла
		 * @var array
		 */
		public static $locale = [];

		/**
		 * Инициализирует языковую систему
		 * @param string $default язык по-умолчанию
		 */
		public static function initialize($default = "ru") {
			$lang = static::$force ? static::$force : App::getClientLang($default);
			$file = static::getLangFilePath(trim($lang));

			if (file_exists($file)) {
				static::$locale = parse_ini_file($file, true);
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
		 * @param string $locale выбранный язык
		 * @return string
		 */
		protected static function getLangFilePath($locale) {
			// директориия хранилища по-умолчанию
			$path = App::get("base_dir") . "/storage/i18n/";

			if (($cache = App::path("lang:")) !== false) {
				$path = $cache;
			}

			return $path . static::$prefix . $locale . ".ini";
		}
	}
}

namespace {

	use Orchid\Extension\i18n;
	use function Orchid\fetch_from_array;

	class L {
		/**
		 * Возвращает интернационализированный текст для указанного ключа
		 * @param $key
		 * @return mixed
		 */
		public static function get($key) {
			return fetch_from_array(i18n::$locale, $key, null);
		}
	}
}