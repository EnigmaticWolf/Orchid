<?php

namespace Orchid;

use Orchid\Entity\Exception\NullPointException;

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
	 * Request constructor.
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
	 * Return client IP address
	 *
	 * @return null
	 */
	public static function getClientIp() {
		$result = null;

		switch (true) {
			case isset($_SERVER["HTTP_X_FORWARDED_FOR"]): {
				$result = $_SERVER["HTTP_X_FORWARDED_FOR"];
				break;
			}
			case isset($_SERVER["HTTP_CLIENT_IP"]): {
				$result = $_SERVER["HTTP_CLIENT_IP"];
				break;
			}
			case isset($_SERVER["REMOTE_ADDR"]): {
				$result = $_SERVER["REMOTE_ADDR"];
				break;
			}
		}

		return $result;
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
	 *
	 * @return mixed
	 * @throws NullPointException
	 */
	public function getHeader($name = "") {
		if ($name) {
			if (isset($this->headers[$name])) {
				return $this->headers[$name];
			}

			throw new NullPointException("Header with key '" . $name . "' not found");
		}

		return $this->headers;
	}

	/**
	 * Return uri by index or array of uri's
	 *
	 * @param string $index
	 *
	 * @return array|mixed
	 * @throws NullPointException
	 */
	public function getUri($index = "") {
		if ($index !== "") {
			if (isset($this->uri[$index])) {
				return $this->uri[$index];
			}

			throw new NullPointException("Uri with index '" . $index . "' not found");
		}

		return $this->uri;
	}

	/**
	 * Return GET parameter or get array
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 * @throws NullPointException
	 */
	public function getParam($key = "") {
		if ($key) {
			if (isset($this->get[$key])) {
				return $this->get[$key];
			}

			throw new NullPointException("GET parameter with key '" . $key . "' not found");
		}

		return $this->get;
	}

	/**
	 * Return POST data or post array
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 * @throws NullPointException
	 */
	public function getData($key = "") {
		if ($key) {
			if (isset($this->post[$key])) {
				return $this->post[$key];
			}

			throw new NullPointException("POST data with key '" . $key . "' not found");
		}

		return $this->post;
	}

	/**
	 * Return FILE data or files array
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 * @throws NullPointException
	 */
	public function getFile($key = "") {
		if ($key) {
			if (isset($this->file[$key])) {
				return $this->file[$key];
			}

			throw new NullPointException("FILE with key '" . $key . "' not found");
		}

		return $this->file;
	}
}