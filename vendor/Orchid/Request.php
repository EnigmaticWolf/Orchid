<?php

namespace Orchid;

class Request {
	const METHOD_HEAD = "HEAD";
	const METHOD_GET = "GET";
	const METHOD_POST = "POST";
	const METHOD_PUT = "PUT";
	const METHOD_PATCH = "PATCH";
	const METHOD_DELETE = "DELETE";
	const METHOD_PURGE = "PURGE";
	const METHOD_OPTIONS = "OPTIONS";
	const METHOD_TRACE = "TRACE";
	const METHOD_CONNECT = "CONNECT";

	/**
	 * Хост
	 *
	 * @var string
	 */
	protected static $host = "";

	/**
	 * Порт
	 *
	 * @var int
	 */
	protected static $port = 80;

	/**
	 * Строка
	 *
	 * @var array
	 */
	protected static $uri = [];

	/**
	 * Метод
	 *
	 * @var string
	 */
	protected static $method = "";

	/**
	 * Параметры
	 *
	 * @var array
	 */
	protected static $param = [];

	/**
	 * POST данные
	 *
	 * @var array
	 */
	protected static $data = [];

	/**
	 * Загруженные файлы
	 *
	 * @var array
	 */
	protected static $file = [];

	/**
	 * Куки
	 *
	 * @var array
	 */
	protected static $cookie = [];

	/**
	 * Сессия
	 *
	 * @var array
	 */
	public static $session = [];

	/**
	 * Массив заголовков
	 *
	 * @var array
	 */
	public static $headers = [];

	/**
	 * Подготавливает параметры запроса для дальнейшего использования
	 *
	 * @param string $query
	 * @param string $method
	 * @param array  $post
	 * @param array  $file
	 * @param array  $cookie
	 * @param array  $session
	 */
	public static function initialize($query, $method = "GET", $post = [], $file = [], $cookie = [], $session = []) {
		$parsed = parse_url(urldecode($query));

		static::$host = isset($parsed["host"]) ? $parsed["host"] : (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null);
		static::$port = isset($parsed["port"]) ? $parsed["port"] : (isset($_SERVER['SERVER_PORT']) ? $_SERVER['SERVER_PORT'] : null);
		static::$uri = [];
		if ($parsed["path"]) {
			foreach (explode("/", $parsed["path"]) as $part) {
				if ($part) {
					static::$uri[] = $part;
				}
			}
		}
		static::$method = $method;
		static::$param = [];
		if (isset($parsed["query"])) {
			parse_str($parsed["query"], $get);
			static::$param = $get;
		}
		static::$data = $post;
		static::$file = $file;
		static::$cookie = $cookie;
		static::$session = $session;

		// наполняем массив заголовков
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == "HTTP_") {
				static::$headers[str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))))] = $value;
			}
		}
	}

	/**
	 * Это Ajax запрос
	 *
	 * @return bool
	 */
	public static function isAjax() {
		return (
			(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") ||
			(isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false) ||
			(isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") !== false)
		);
	}

	/**
	 * Это защищенное соединение (HTTPS)
	 *
	 * @return bool
	 */
	public static function isSecure() {
		return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
	}

	/**
	 * Возвращает значение заголовка по ключу
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function getHeader($key) {
		return isset(static::$headers[$key]) ? static::$headers[$key] : null;
	}

	/**
	 * Возвращает весь список заголовков
	 *
	 * @return array
	 */
	public static function getHeaderList() {
		return static::$headers;
	}

	/**
	 * Возвращает значение из строки запроса по индексу
	 *
	 * @param int $index
	 *
	 * @return mixed
	 */
	public static function getUri($index) {
		return isset(static::$uri[$index]) ? static::$uri[$index] : null;
	}

	/**
	 * Возвращает весь список
	 *
	 * @return array
	 */
	public static function getUriList() {
		return static::$uri;
	}

	/**
	 * Возвращает текущий метод
	 *
	 * @return string
	 */
	public static function getMethod() {
		return static::$method;
	}

	/**
	 * Проверяет текущий метод на соответствие указанному
	 *
	 * @param $method
	 *
	 * @return bool
	 */
	public static function isMethod($method) {
		return (strtoupper(static::$method) == $method);
	}

	/**
	 * Возвращает GET параметр по ключу
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function getParam($key) {
		return isset(static::$param[$key]) ? static::$param[$key] : null;
	}

	/**
	 * Возвращает весь список GET параметров
	 *
	 * @return array
	 */
	public static function getParamList() {
		return static::$param;
	}

	/**
	 * Возвращает POST параметр по ключу
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public static function getData($key) {
		return isset(static::$data[$key]) ? static::$data[$key] : null;
	}

	/**
	 * Возвращает весь список POST параметров
	 *
	 * @return array
	 */
	public static function getDataList() {
		return static::$data;
	}

	/**
	 * Возвращает массив с данными загруженного файла по ключу
	 *
	 * @param string $key
	 *
	 * @return array
	 */
	public static function getFile($key) {
		return isset(static::$file[$key]) ? static::$file[$key] : null;
	}

	/**
	 * Возвращает весь список загруженных файлов
	 *
	 * @return array
	 */
	public static function getAllFiles() {
		return static::$file;
	}

	/**
	 * Возвращает IP адрес клиента
	 *
	 * @return string|null
	 */
	public static function getClientIp() {
		switch (true) {
			case isset($_SERVER["HTTP_X_FORWARDED_FOR"]): {
				return $_SERVER["HTTP_X_FORWARDED_FOR"];
			}
			case isset($_SERVER["HTTP_CLIENT_IP"]): {
				return $_SERVER["HTTP_CLIENT_IP"];
			}
			case isset($_SERVER["REMOTE_ADDR"]): {
				return $_SERVER["REMOTE_ADDR"];
			}
		}

		return null;
	}

	/**
	 * Возвращает наиболее подходящий язык браузера клиента из заданных в locale
	 *
	 * @param string $default по умолчанию русский
	 *
	 * @return string
	 */
	public static function getClientLang($default = "ru") {
		if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]) && ($list = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]))) {
			if (preg_match_all("/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/", $list, $list)) {
				$language = [];

				foreach (array_combine($list[1], $list[2]) as $lang => $priority) {
					$language[$lang] = (float)($priority ? $priority : 1);
				}
				arsort($language, SORT_NUMERIC);

				foreach ($language as $lang => $priority) {
					if (in_array($lang, App::getLocaleList())) {
						return $lang;
					}
				}
			}
		}

		return $default;
	}

	/**
	 * Возвращает схему
	 *
	 * @return string
	 */
	public static function getScheme() {
		return Request::isSecure() ? "https" : "http";
	}

	/**
	 * Возвращает субдомен
	 *
	 * Если текущее приложение public, то будет возвращена пустая строка
	 *
	 * @param string $app принудительный выбор приложения
	 *
	 * @return string
	 */
	public static function getSubdomain($app = "") {
		if (($app = (!$app ? App::getApp() : $app)) && $app != "public") {
			return $app . ".";
		}

		return "";
	}

	/**
	 * Возвращает базовое имя хоста
	 *
	 * @return mixed
	 */
	public static function getHost() {
		return App::getBaseHost();
	}

	/**
	 * Возвращает порт
	 *
	 * Если порт текущий 80, то будет возвращена пустая строка
	 *
	 * @return string
	 */
	public static function getPort() {
		if (($port = App::getBasePort()) != 80) {
			return ":" . $port;
		}

		return "";
	}

	/**
	 * Возвращает путь
	 *
	 * @return string
	 */
	public static function getPath() {
		return "/" . implode("/", static::$uri);
	}

	/**
	 * Возвращает адрес страницы
	 *
	 * @param string $app
	 * @param bool   $withPath вернуть адрес со строкой запроса
	 *
	 * @return string
	 */
	public static function getUrl($app = "", $withPath = false) {
		$url = static::getScheme() . "://" . static::getSubdomain($app) . static::getHost() . static::getPort();

		if ($withPath) {
			$url .= Request::getPath();
		}

		return rtrim($url, "/");
	}
}