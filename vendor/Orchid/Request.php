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
	 * Подготавливает параметры запроса для дальнейшего использования
	 *
	 * @param string $query
	 * @param array  $post
	 * @param array  $file
	 * @param array  $cookie
	 */
	public static function initialize($query, $post = [], $file = [], $cookie = []) {
		// заполняем URI
		$uri = [];
		foreach (explode("/", parse_url(urldecode($query), PHP_URL_PATH)) as $part) {
			if ($part) {
				$uri[] = $part;
			}
		}
		App::set("uri", $uri);

		// переписываем GET
		$get = [];
		foreach (explode("&", parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY)) as $part) {
			if ($part) {
				$data = explode("=", $part);
				$get[$data[0]] = isset($data[1]) ? $data[1] : "";
			}
		}
		App::set("param", $get);

		// проверяем php://input и объединяем с $_POST
		if (Request::is("ajax")) {
			if ($json = json_decode(@file_get_contents("php://input"), true)) {
				$post = array_merge($_POST, $json);
			}
		}
		App::set("data", $post);
		App::set("file", $file);
		App::set("cookie", $cookie);

		$_GET = $get;
		$_POST = $get;
		$_COOKIE = $cookie;
		$_REQUEST = array_merge($get, $post, $cookie);
	}

	/**
	 * @param $type
	 *
	 * @return bool|int
	 */
	public static function is($type) {
		switch (strtolower($type)) {
			case "ajax": {
				return (
					(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") ||
					(isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false) ||
					(isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") !== false)
				);
			}
			case "mobile": {
				$mobileDevices = [
					"midp", "240x320", "blackberry", "netfront", "nokia", "panasonic", "portalmmm",
					"sharp", "sie-", "sonyericsson", "symbian", "windows ce", "benq", "mda", "mot-",
					"opera mini", "philips", "pocket pc", "sagem", "samsung", "sda", "sgh-", "vodafone",
					"xda", "iphone", "ipod", "android",
				];

				return preg_match("/(" . implode("|", $mobileDevices) . ")/i", strtolower($_SERVER["HTTP_USER_AGENT"]));
			}
			case "head": {
				return (strtolower(App::get("method")) == "head");
			}
			case "put": {
				return (strtolower(App::get("method")) == "put");
			}
			case "post": {
				return (strtolower(App::get("method")) == "post");
			}
			case "get": {
				return (strtolower(App::get("method")) == "get");
			}
			case "delete": {
				return (strtolower(App::get("method")) == "delete");
			}
			case "options": {
				return (strtolower(App::get("method")) == "options");
			}
			case "ssl": {
				return (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off");
			}
		}

		return false;
	}

	/**
	 * Возвращает IP адрес клиента
	 *
	 * @return string
	 */
	public static function getClientIp() {
		if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
			return $_SERVER["HTTP_X_FORWARDED_FOR"];
		} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
			return $_SERVER["HTTP_CLIENT_IP"];
		} elseif (isset($_SERVER["REMOTE_ADDR"])) {
			return $_SERVER["REMOTE_ADDR"];
		}

		return false;
	}

	/**
	 * Возвращает наиболее подходящий язык клиента из заданных в locale
	 *
	 * @param string $default по умолчанию русский
	 *
	 * @return string
	 */
	public static function getClientLang($default = "ru") {
		if (($list = strtolower($_SERVER["HTTP_ACCEPT_LANGUAGE"]))) {
			if (preg_match_all("/([a-z]{1,8}(?:-[a-z]{1,8})?)(?:;q=([0-9.]+))?/", $list, $list)) {
				$language = [];

				foreach (array_combine($list[1], $list[2]) as $lang => $priority) {
					$language[$lang] = (float)($priority ? $priority : 1);
				}
				arsort($language, SORT_NUMERIC);

				foreach ($language as $lang => $priority) {
					if (in_array($lang, App::get("locale"))) {
						return $lang;
					}
				}
			}
		}

		return $default;
	}

	/**
	 * Возвращает адрес сайта
	 *
	 * @param bool   $withPath
	 * @param string $app
	 *
	 * @return string
	 */
	public static function getSiteUrl($withPath = false, $app = "") {
		$url = (Request::is("ssl") ? "https" : "http") . "://";
		if (($app = (empty($app) ? App::get("app") : $app)) && $app != "public") {
			$url .= $app . ".";
		}
		$url .= App::get("base_host");
		if (($port = App::get("base_port")) != 80) {
			$url .= ":" . $port;
		}
		if ($withPath) {
			$url .= Request::getSitePath();
		}

		return rtrim($url, "/");
	}

	/**
	 * Возвращает путь
	 *
	 * @return string
	 */
	public static function getSitePath() {
		return "/" . implode("/", App::get("uri"));
	}
}