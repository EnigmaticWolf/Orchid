<?php

namespace Orchid;

use DateTime;
use DateTimeZone;

class Response {
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_PROCESSING = 102;
	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	const HTTP_MULTI_STATUS = 207;
	const HTTP_ALREADY_REPORTED = 208;
	const HTTP_IM_USED = 226;
	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_FOUND = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_RESERVED = 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	const HTTP_PERMANENTLY_REDIRECT = 308;
	const HTTP_BAD_REQUEST = 400;
	const HTTP_UNAUTHORIZED = 401;
	const HTTP_PAYMENT_REQUIRED = 402;
	const HTTP_FORBIDDEN = 403;
	const HTTP_NOT_FOUND = 404;
	const HTTP_METHOD_NOT_ALLOWED = 405;
	const HTTP_NOT_ACCEPTABLE = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
	const HTTP_REQUEST_TIMEOUT = 408;
	const HTTP_CONFLICT = 409;
	const HTTP_GONE = 410;
	const HTTP_LENGTH_REQUIRED = 411;
	const HTTP_PRECONDITION_FAILED = 412;
	const HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
	const HTTP_REQUEST_URI_TOO_LONG = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
	const HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
	const HTTP_EXPECTATION_FAILED = 417;
	const HTTP_I_AM_A_TEAPOT = 418;
	const HTTP_UNPROCESSABLE_ENTITY = 422;
	const HTTP_LOCKED = 423;
	const HTTP_FAILED_DEPENDENCY = 424;
	const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425;
	const HTTP_UPGRADE_REQUIRED = 426;
	const HTTP_PRECONDITION_REQUIRED = 428;
	const HTTP_TOO_MANY_REQUESTS = 429;
	const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;
	const HTTP_INSUFFICIENT_STORAGE = 507;
	const HTTP_LOOP_DETECTED = 508;
	const HTTP_NOT_EXTENDED = 510;
	const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;

	/**
	 * Массив заголовков ответа
	 *
	 * @var array
	 */
	public static $headers;

	/**
	 * Флаг выключающий кеширование ответа
	 *
	 * @var bool
	 */
	public static $nocache = false;

	/**
	 * Содержимое ответа
	 *
	 * @var string
	 */
	protected static $content;

	/**
	 * Тип содержимого
	 *
	 * @var string
	 */
	protected static $contentType;

	/**
	 * Код состояния ответа
	 *
	 * @var int
	 */
	protected static $statusCode;

	/**
	 * Текстовое представление кода состояния ответа
	 *
	 * @var string
	 */
	protected static $statusText;

	/**
	 * Кодировка ответа
	 *
	 * @var string
	 */
	protected static $charset = "UTF-8";

	/**
	 * Переводная таблица кодов статус
	 *
	 * @var array
	 */
	public static $statusTexts = [
		100 => "Continue",
		101 => "Switching Protocols",
		102 => "Processing",
		200 => "OK",
		201 => "Created",
		202 => "Accepted",
		203 => "Non-Authoritative Information",
		204 => "No Content",
		205 => "Reset Content",
		206 => "Partial Content",
		207 => "Multi-Status",
		208 => "Already Reported",
		226 => "IM Used",
		300 => "Multiple Choices",
		301 => "Moved Permanently",
		302 => "Found",
		303 => "See Other",
		304 => "Not Modified",
		305 => "Use Proxy",
		307 => "Temporary Redirect",
		308 => "Permanent Redirect",
		400 => "Bad Request",
		401 => "Unauthorized",
		402 => "Payment Required",
		403 => "Forbidden",
		404 => "Not Found",
		405 => "Method Not Allowed",
		406 => "Not Acceptable",
		407 => "Proxy Authentication Required",
		408 => "Request Timeout",
		409 => "Conflict",
		410 => "Gone",
		411 => "Length Required",
		412 => "Precondition Failed",
		413 => "Payload Too Large",
		414 => "URI Too Long",
		415 => "Unsupported Media Type",
		416 => "Range Not Satisfiable",
		417 => "Expectation Failed",
		418 => "I'm a teapot",
		422 => "Unprocessable Entity",
		423 => "Locked",
		424 => "Failed Dependency",
		425 => "Reserved for WebDAV advanced collections expired proposal",
		426 => "Upgrade Required",
		428 => "Precondition Required",
		429 => "Too Many Requests",
		431 => "Request Header Fields Too Large",
		451 => "Unavailable For Legal Reasons",
		500 => "Internal Server Error",
		501 => "Not Implemented",
		502 => "Bad Gateway",
		503 => "Service Unavailable",
		504 => "Gateway Timeout",
		505 => "HTTP Version Not Supported",
		506 => "Variant Also Negotiates (Experimental)",
		507 => "Insufficient Storage",
		508 => "Loop Detected",
		510 => "Not Extended",
		511 => "Network Authentication Required",
	];

	/**
	 * Переводная таблица типов данных
	 *
	 * @var array
	 */
	public static $mimeTypes = [
		//Texts
		"txt"    => "text/plain",
		"ini"    => "text/ini",
		"config" => "text/xml",

		//WWW
		"htm"    => "text/html",
		"html"   => "text/html",
		"tpl"    => "text/html",
		"css"    => "text/css",
		"less"   => "text/css",
		"js"     => "application/x-javascript",
		"json"   => "application/json",
		"xml"    => "application/xml",
		"swf"    => "application/x-shockwave-flash",

		//Images
		"jpe"    => "image/jpeg",
		"jpg"    => "image/jpeg",
		"jpeg"   => "image/jpeg",
		"png"    => "image/png",
		"bmp"    => "image/bmp",
		"gif"    => "image/gif",
		"tif"    => "image/tiff",
		"tiff"   => "image/tiff",
		"ico"    => "image/vnd.microsoft.icon",
		"svg"    => "image/svg+xml",
		"svgz"   => "image/svg+xml",

		//Fonts
		"eot"    => "application/vnd.ms-fontobject",
		"ttf"    => "application/font-ttf",
		"woff"   => "application/font-woff",

		//Audio
		"flac"   => "audio/x-flac",
		"mp3"    => "audio/mpeg",
		"wav"    => "audio/wav",
		"wma"    => "audio/x-ms-wma",

		//Video
		"qt"     => "video/quicktime",
		"mov"    => "video/quicktime",
		"mkv"    => "video/mkv",
		"mp4"    => "video/mp4",

		//Archive
		"7z"     => "application/x-7z-compressed",
		"zip"    => "application/x-zip-compressed",
		"rar"    => "application/x-rar-compressed",

		//Application
		"jar"    => "application/java-archive",
		"java"   => "application/octet-stream",
		"exe"    => "application/octet-stream",
		"msi"    => "application/octet-stream",
		"dll"    => "application/x-msdownload",
	];

	/**
	 * Генерирует ответ
	 *
	 * @param  mixed $content
	 * @param int    $status
	 * @param array  $headers
	 */
	public static function create($content = "", $status = 200, array $headers = []) {
		static::setContent($content);
		static::setContentType("html");
		static::setStatus($status);

		static::$headers = [];
		foreach ($headers as $key => $value) {
			static::setHeader($key, $value);
		}
	}

	/**
	 * Добавляет заголовок в массив заголовков ответа
	 *
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function setHeader($key, $value) {
		static::$headers[$key] = $value;
	}

	/**
	 * Добавляет заголовок Date
	 *
	 * @param DateTime $date
	 */
	public static function setHeaderDate(DateTime $date) {
		$date->setTimezone(new DateTimeZone("UTC"));

		static::setHeader("Date", $date->format("D, d M Y H:i:s") . " GMT");
	}

	/**
	 * Добавляет заголовок Expires
	 *
	 * @param DateTime $date
	 */
	public static function setHeaderExpires(DateTime $date) {
		$date->setTimezone(new DateTimeZone("UTC"));

		static::setHeader("Expires", $date->format("D, d M Y H:i:s") . " GMT");
	}

	/**
	 * Добавляет заголовок Cache-Control
	 *
	 * Для установки правильного значения необходимо сложить вес необходимых аргументов
	 * Вес и значения:
	 *  2   - public
	 *  4   - private
	 *  8   - no-cache
	 *  16  - no-store
	 *  32  - must-revalidate
	 *  64  - proxy-revalidate
	 *  128 - no-transform
	 *  256 - max-age=
	 *  512 - s-maxage=
	 *
	 * @param int $bitWeight
	 * @param int $maxAge       время жизни для Cache-Control/max-age
	 * @param int $sharedMaxAge время жизни для Cache-Control/s-maxage
	 */
	public static function setHeaderCacheControl($bitWeight = 0, $maxAge = 3600, $sharedMaxAge = 600) {
		$valuesByWeight = [
			512 => "s-maxage=" . $sharedMaxAge,
			256 => "max-age=" . $maxAge,
			128 => "no-transform",
			64  => "proxy-revalidate",
			32  => "must-revalidate",
			16  => "no-store",
			8   => "no-cache",
			4   => "private",
			2   => "public",
		];

		$header = [];
		foreach ($valuesByWeight as $weight => $attr) {
			if ($weight & $bitWeight) {
				$header[] = $attr;
			}
		}

		static::setHeader("Cache-Control", implode(" ", array_reverse($header)));
	}

	/**
	 * Устанавливает состояние NotModified
	 */
	public static function setHeaderNotModified() {
		static::setStatus(Response::HTTP_NOT_MODIFIED);
		static::setContent(null);

		// удаляет заголовки которых быть недолжно
		foreach (["Allow", "Content-Encoding", "Content-Language", "Content-Length", "Content-MD5", "Content-Type", "Last-Modified"] as $header) {
			static::deleteHeader($header);
		}
	}

	/**
	 * Возвращает значение заголовка по ключу
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 */
	public static function getHeader($key) {
		return isset(static::$headers[$key]) ? static::$headers[$key] : null;
	}

	/**
	 * Возвращает весь список заголовков
	 *
	 * @return array
	 */
	public static function getAllHeaders() {
		return static::$headers;
	}

	/**
	 * Удаляет указанный заголовок
	 *
	 * @param $key
	 */
	public static function deleteHeader($key) {
		unset(static::$headers[$key]);
	}

	/**
	 * Устанавливает содержимое ответа
	 *
	 * Содержимое должно быть:
	 *  - null
	 *  - массиовом
	 *  - строкой
	 *  - числом
	 *  - объектом с методом __toString
	 *
	 * @param mixed $content
	 */
	public static function setContent($content = "") {
		if (is_array($content)) {
			$content = json_encode($content);
		}

		if ($content === null || ($content && is_string($content) || is_numeric($content) || is_callable([$content, "__toString"]))) {
			static::setHeader("Content-Length", mb_strlen($content));
			static::$content = $content;
		}
	}

	/**
	 * Возвращает содержимое ответа
	 *
	 * @return mixed
	 */
	public static function getContent() {
		return static::$content;
	}

	/**
	 * Устанавливает тип содержимого ответа
	 *
	 * @param $contentType
	 */
	public static function setContentType($contentType) {
		static::$contentType = isset(static::$mimeTypes[$contentType]) ? static::$mimeTypes[$contentType] : "text/plain";
	}

	/**
	 * Возвращает тип содержимого ответа
	 *
	 * @return string
	 */
	public static function getContentType() {
		return static::$contentType;
	}

	/**
	 * Устанавливает код состояния ответа
	 *
	 * @param $status
	 */
	public static function setStatus($status) {
		static::$statusCode = $status;
		static::$statusText = isset(static::$statusTexts[$status]) ? static::$statusTexts[$status] : "unknown";
	}

	/**
	 * Возвращает код состояния ответа
	 *
	 * @return mixed
	 */
	public static function getStatus() {
		return static::$statusCode;
	}

	/**
	 * Возвращает текстовое представление кода состояния ответа
	 *
	 * @return mixed
	 */
	public static function getStatusText() {
		return static::$statusText;
	}

	/**
	 * Устанавливает кодировку ответа
	 *
	 * @param string $charset
	 */
	public static function setCharset($charset) {
		static::$charset = $charset;
	}

	/**
	 * Возвращает кодировку ответа
	 *
	 * @return string mixed
	 */
	public static function getCharset() {
		return static::$charset;
	}

	/**
	 * Проверяет был ли изменён ответ сравнивая заголовки: ETag, Last-Modified
	 *
	 * Если ответ небыл изменён, то статус будет изменён на 304, а ответ будет удалён
	 *
	 * @return bool
	 */
	public static function isNotModified() {
		if (!Request::is(Request::METHOD_GET) && !Request::is(Request::METHOD_HEAD)) {
			return false;
		}

		$notModified = false;
		$lastModified = static::getHeader("Last-Modified");
		$modifiedSince = Request::getHeader("If-Modified-Since");

		if ($etags = Request::getHeader("If-None-Match")) {
			$notModified = static::getHeader("ETag") == $etags;
		}

		if ($modifiedSince && $lastModified) {
			$notModified = strtotime($modifiedSince) >= strtotime($lastModified) && (!$etags || $notModified);
		}

		if ($notModified) {
			static::setHeaderNotModified();
		}

		return $notModified;
	}

	/**
	 * Код ответа задан с ошибкой
	 *
	 * @return bool
	 */
	public static function isInvalid() {
		return static::$statusCode < 100 || static::$statusCode >= 600;
	}

	/**
	 * Является ли ответ информативным?
	 *
	 * @return bool
	 */
	public static function isInformational() {
		return static::$statusCode >= 100 && static::$statusCode < 200;
	}

	/**
	 * Является ли ответ успешным?
	 *
	 * @return bool
	 */
	public static function isSuccessful() {
		return static::$statusCode >= 200 && static::$statusCode < 300;
	}

	/**
	 * Является ли ответ перенаправлением?
	 *
	 * @return bool
	 */
	public static function isRedirection() {
		return static::$statusCode >= 300 && static::$statusCode < 400;
	}

	/**
	 * Есть ли ошибка клиента?
	 *
	 * @return bool
	 */
	public static function isClientError() {
		return static::$statusCode >= 400 && static::$statusCode < 500;
	}

	/**
	 * Есть ли серверная ошибка?
	 *
	 * @return bool
	 */
	public static function isServerError() {
		return static::$statusCode >= 500 && static::$statusCode < 600;
	}

	/**
	 * Является ли ответ OK?
	 *
	 * @return bool
	 */
	public static function isOk() {
		return static::$statusCode === 200;
	}

	/**
	 * Является ли ответ Запрещено?
	 *
	 * @return bool
	 */
	public static function isForbidden() {
		return static::$statusCode === 403;
	}

	/**
	 * Является ли ответ ошибкой Не найден?
	 *
	 * @return bool
	 */
	public static function isNotFound() {
		return static::$statusCode === 404;
	}

	/**
	 * Является ли ответ пустым?
	 *
	 * @return mixed
	 */
	public static function isEmpty() {
		return in_array(static::$statusCode, [204, 304]);
	}

	/**
	 * Отправляет ответ клиенту
	 */
	public static function send() {
		if (!headers_sent()) {
			static::sendHeaders();
			static::sendContent();
		}
	}

	/**
	 * Отправляет заголовки
	 */
	protected static function sendHeaders() {
		// дата
		if (!isset(static::$headers["Date"])) {
			static::setHeaderDate(DateTime::createFromFormat("U", time()));
		}
		// тип и кодировка
		if (!isset(static::$headers["Content-Type"])) {
			static::setHeader("Content-Type", static::$contentType . "; charset=" . static::$charset);
		}

		// если ответ информационный, перенаправление или пуст
		if (static::isInformational() || static::isRedirection() || static::isEmpty()) {
			static::setContent(null);
			static::deleteHeader("Content-Type");
			static::deleteHeader("Content-Length");
		}

		if (static::getHeader("Transfer-Encoding")) {
			static::deleteHeader("Content-Length");
		}

		// см. RFC2616 14.13
		if (Request::is(Request::METHOD_HEAD)) {
			$length = static::getHeader("Content-Length");
			static::setContent(null);

			if ($length) {
				static::setHeader("Content-Length", $length);
			}
		}

		// заголовки
		foreach (static::$headers as $key => $value) {
			header($key . ": " . $value, false, static::$statusCode);
		}

		// статус
		header(sprintf("HTTP/1.1 %s %s", static::$statusCode, static::$statusText), true, static::$statusCode);
	}

	/**
	 * Отправляет содержимое ответа
	 */
	protected static function sendContent() {
		echo static::$content;
	}
}