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
	 * @var array
	 */
	protected static $formats = [
		//Texts
		"txt"    => ["text/plain"],
		"ini"    => ["text/ini"],
		"config" => ["text/xml"],

		//WWW
		"htm"    => ["text/html", "application/xhtml+xml"],
		"html"   => ["text/html", "application/xhtml+xml"],
		"tpl"    => ["text/html", "application/xhtml+xml"],
		"css"    => ["text/css"],
		"less"   => ["text/css"],
		"js"     => ["application/javascript", "application/x-javascript", "text/javascript"],
		"json"   => ["application/json", "application/x-json"],
		"xml"    => ["text/xml", "application/xml", "application/x-xml"],
		"swf"    => ["application/x-shockwave-flash"],
		"rdf"    => ["application/rdf+xml"],
		"atom"   => ["application/atom+xml"],
		"rss"    => ["application/rss+xml"],
		"form"   => ["application/x-www-form-urlencoded"],

		//Images
		"jpe"    => ["image/jpeg"],
		"jpg"    => ["image/jpeg"],
		"jpeg"   => ["image/jpeg"],
		"png"    => ["image/png"],
		"bmp"    => ["image/bmp"],
		"gif"    => ["image/gif"],
		"tif"    => ["image/tiff"],
		"tiff"   => ["image/tiff"],
		"ico"    => ["image/vnd.microsoft.icon"],
		"svg"    => ["image/svg+xml"],
		"svgz"   => ["image/svg+xml"],

		//Fonts
		"eot"    => ["application/vnd.ms-fontobject"],
		"ttf"    => ["application/font-ttf"],
		"woff"   => ["application/font-woff"],

		//Audio
		"flac"   => ["audio/x-flac"],
		"mp3"    => ["audio/mpeg"],
		"wav"    => ["audio/wav"],
		"wma"    => ["audio/x-ms-wma"],

		//Video
		"qt"     => ["video/quicktime"],
		"mov"    => ["video/quicktime"],
		"mkv"    => ["video/mkv"],
		"mp4"    => ["video/mp4"],

		//Archive
		"7z"     => ["application/x-7z-compressed"],
		"zip"    => ["application/x-zip-compressed"],
		"rar"    => ["application/x-rar-compressed"],

		//Application
		"jar"    => ["application/java-archive"],
		"java"   => ["application/octet-stream"],
		"exe"    => ["application/octet-stream"],
		"msi"    => ["application/octet-stream"],
		"dll"    => ["application/x-msdownload"],
	];

	/**
	 * Flag is HTTPS
	 *
	 * @var bool
	 */
	protected $secure = false;

	/**
	 * Request host
	 *
	 * @var array
	 */
	protected $host = [];

	/**
	 * Request port
	 *
	 * @var int
	 */
	protected $port = 0;

	/**
	 * Request method
	 *
	 * @var string
	 */
	protected $method = null;

	/**
	 * Array of GET parameters
	 *
	 * @var array
	 */
	protected $get = [];

	/**
	 * Array of POST data
	 *
	 * @var array
	 */
	protected $post = [];

	/**
	 * Array of URI data
	 *
	 * @var array
	 */
	protected $uri = [];

	/**
	 * Array of Cookie
	 *
	 * @var array
	 */
	protected $cookie = [];

	/**
	 * Array of headers
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Request constructor
	 *
	 * @param array $post
	 * @param array $file
	 * @param array $cookie
	 */
	public function __construct($post = [], $file = [], $cookie = []) {
		$this->secure = strtolower($_SERVER["REQUEST_SCHEME"]) == "https";
		$this->host = $_SERVER['HTTP_HOST'];
		$this->port = $_SERVER["SERVER_PORT"];
		$this->method = strtoupper($_SERVER["REQUEST_METHOD"]);

		$url = parse_url($_SERVER['REQUEST_URI']);
		if (isset($url["query"])) {
			parse_str($url["query"], $this->get);
		}
		if (isset($url["path"])) {
			foreach (explode("/", $url["path"]) as $part) {
				if ($part) {
					$this->uri[] = $part;
				}
			}
		}

		$this->post = $post ? $post : $_POST;
		$this->file = $file ? $file : $_FILES;
		$this->cookie = $cookie ? $cookie : $_COOKIE;

		// fill an array of headers
		foreach ($_SERVER as $name => $value) {
			if (substr($name, 0, 5) == "HTTP_") {
				$this->headers[str_replace(" ", "-", ucwords(strtolower(str_replace("_", " ", substr($name, 5)))))] = $value;
			}
		}
	}

	/**
	 * Get mime type associated with format
	 *
	 * @param $format
	 *
	 * @return string
	 */
	public function getMimeType($format) {
		return isset(static::$formats[$format]) ? static::$formats[$format][0] : null;
	}

	/**
	 * Get mime types associated with format
	 *
	 * @param $format
	 *
	 * @return array
	 */
	public static function getMimeTypes($format) {
		return isset(static::$formats[$format]) ? static::$formats[$format] : [];
	}

	/**
	 * Get request format by header Accept
	 *
	 * @param string $default
	 *
	 * @return mixed|string
	 */
	public function getRequestFormat($default = "text/html") {
		preg_match_all("~(?<type>(?:\w+|\*)\/(?:\w+|\*))(?:\;q=(?<q>\d(?:\.\d|))|)[\,]{0,}~i", $this->getHeader("Accept"), $list);

		$data = [];
		foreach (array_combine($list["type"], $list["q"]) as $key => $priority) {
			$data[$key] = (float)($priority ? $priority : 1);
		}
		arsort($data, SORT_NUMERIC);

		return $data ? key($data) : $default;
	}

	/**
	 * Return client IP address
	 *
	 * @return null
	 */
	public static function getClientIp() {
		switch (true) {
			case isset($_SERVER["HTTP_X_FORWARDED_FOR"]): {
				return $_SERVER["HTTP_X_FORWARDED_FOR"];
				break;
			}
			case isset($_SERVER["HTTP_CLIENT_IP"]): {
				return $_SERVER["HTTP_CLIENT_IP"];
				break;
			}
			case isset($_SERVER["REMOTE_ADDR"]): {
				return $_SERVER["REMOTE_ADDR"];
				break;
			}
		}

		return null;
	}

	/**
	 * Return method type
	 *
	 * @return string
	 */
	public function getMethod() {
		return $this->method;
	}

	/**
	 * Check request method
	 *
	 * @param $method
	 *
	 * @return bool
	 */
	public function isMethod($method) {
		return $this->method == $method;
	}

	/**
	 * Check AJAX request
	 *
	 * @return bool
	 */
	public function isAjax() {
		return (
			(isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == "XMLHttpRequest") ||
			(isset($_SERVER["CONTENT_TYPE"]) && stripos($_SERVER["CONTENT_TYPE"], "application/json") !== false) ||
			(isset($_SERVER["HTTP_CONTENT_TYPE"]) && stripos($_SERVER["HTTP_CONTENT_TYPE"], "application/json") !== false)
		);
	}

	/**
	 * Check is secure request (HTTPS)
	 *
	 * @return bool
	 */
	public function isSecure() {
		return $this->secure;
	}

	/**
	 * Return header value by name or array of headers
	 *
	 * @param string $name
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	public function getHeader($name = "", $default = null) {
		if ($name) {
			if (isset($this->headers[$name])) {
				return $this->headers[$name];
			}

			return $default;
		}

		return $this->headers;
	}

	/**
	 * Return uri by index or array of uri's
	 *
	 * @param string $index
	 * @param mixed  $default
	 *
	 * @return array|mixed
	 */
	public function getUri($index = "", $default = null) {
		if ($index !== "") {
			if (isset($this->uri[$index])) {
				return $this->uri[$index];
			}

			return $default;
		}

		return $this->uri;
	}

	/**
	 * Return current pathname
	 *
	 * @return string
	 */
	public function getPathname() {
		return "/" . implode("/", $this->uri);
	}

	/**
	 * Return GET parameter or get array
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return array|mixed
	 */
	public function getParam($key = "", $default = null) {
		if ($key) {
			if (isset($this->get[$key])) {
				return $this->get[$key];
			}

			return $default;
		}

		return $this->get;
	}

	/**
	 * Return POST data or post array
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return array|mixed
	 */
	public function getData($key = "", $default = null) {
		if ($key) {
			if (isset($this->post[$key])) {
				return $this->post[$key];
			}

			return $default;
		}

		return $this->post;
	}

	/**
	 * Return FILE data or files array
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return array|mixed
	 */
	public function getFile($key = "", $default = null) {
		if ($key) {
			if (isset($this->file[$key])) {
				return $this->file[$key];
			}

			return $default;
		}

		return $this->file;
	}
}