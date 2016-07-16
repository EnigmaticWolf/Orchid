<?php

namespace Orchid;

use DateTime;
use DateTimeZone;
use InvalidArgumentException;
use UnexpectedValueException;
use Orchid\Entity\Exception\NullPointException;

class Response {
	// informational 1xx
	const HTTP_CONTINUE = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_PROCESSING = 102; // RFC2518

	// successful 2xx
	const HTTP_OK = 200;
	const HTTP_CREATED = 201;
	const HTTP_ACCEPTED = 202;
	const HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT = 204;
	const HTTP_RESET_CONTENT = 205;
	const HTTP_PARTIAL_CONTENT = 206;
	const HTTP_MULTI_STATUS = 207; // RFC4918
	const HTTP_ALREADY_REPORTED = 208; // RFC5842
	const HTTP_IM_USED = 226; // RFC3229

	// redirection 3xx
	const HTTP_MULTIPLE_CHOICES = 300;
	const HTTP_MOVED_PERMANENTLY = 301;
	const HTTP_FOUND = 302;
	const HTTP_SEE_OTHER = 303;
	const HTTP_NOT_MODIFIED = 304;
	const HTTP_USE_PROXY = 305;
	const HTTP_RESERVED = 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	const HTTP_PERMANENTLY_REDIRECT = 308; // RFC7238

	// client error 4xx
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
	const HTTP_I_AM_A_TEAPOT = 418; // RFC2324
	const HTTP_MISDIRECTED_REQUEST = 421; // RFC7540
	const HTTP_UNPROCESSABLE_ENTITY = 422; // RFC4918
	const HTTP_LOCKED = 423;  // RFC4918
	const HTTP_FAILED_DEPENDENCY = 424; // RFC4918
	const HTTP_RESERVED_FOR_WEBDAV_ADVANCED_COLLECTIONS_EXPIRED_PROPOSAL = 425; // RFC2817
	const HTTP_UPGRADE_REQUIRED = 426; // RFC2817
	const HTTP_PRECONDITION_REQUIRED = 428; // RFC6585
	const HTTP_TOO_MANY_REQUESTS = 429; // RFC6585
	const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431; // RFC6585
	const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;

	// server error 5xx
	const HTTP_INTERNAL_SERVER_ERROR = 500;
	const HTTP_NOT_IMPLEMENTED = 501;
	const HTTP_BAD_GATEWAY = 502;
	const HTTP_SERVICE_UNAVAILABLE = 503;
	const HTTP_GATEWAY_TIMEOUT = 504;
	const HTTP_VERSION_NOT_SUPPORTED = 505;
	const HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506; // RFC2295
	const HTTP_INSUFFICIENT_STORAGE = 507; // RFC4918
	const HTTP_LOOP_DETECTED = 508; // RFC5842
	const HTTP_NOT_EXTENDED = 510; // RFC2774
	const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511; // RFC6585

	/**
	 * Status codes
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
	 * Headers for the response
	 *
	 * @var array
	 */
	protected $headers = [];

	/**
	 * Response status code
	 *
	 * @var int
	 */
	protected $statusCode;

	/**
	 * Response status text
	 *
	 * @var string
	 */
	protected $statusText;

	/**
	 * Response charset
	 *
	 * @var string
	 */
	protected $charset;

	/**
	 * Type of response content
	 *
	 * @var string
	 */
	protected $contentType;

	/**
	 * Response body
	 *
	 * @var string
	 */
	protected $body;

	/**
	 * Response constructor
	 *
	 * @param string $body
	 * @param int    $status
	 * @param array  $headers
	 */
	public function __construct($body = null, $status = 200, array $headers = []) {
		$this->setContent($body);
		$this->setStatus($status);
		$this->setCharset("UTF-8");

		foreach ($headers as $key => $value) {
			$this->setHeader($key, $value);
		}
	}

	/**
	 * Create Response
	 *
	 * <code>
	 * return Response::create($body, 200)
	 *                  ->setHeaderCacheControl(10);
	 * </code>
	 *
	 * @param string $body
	 * @param int    $status
	 * @param array  $headers
	 *
	 * @return static
	 */
	public static function create($body = null, $status = 200, array $headers = []) {
		return new static($body, $status, $headers);
	}

	/**
	 * Set header HTTP header
	 *
	 * @param $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function setHeader($key, $value) {
		$this->headers[$key] = $value;

		return $this;
	}

	/**
	 * Set the Date HTTP header with a DateTime instance
	 *
	 * @param DateTime $date
	 *
	 * @return $this
	 */
	public function setHeaderDate(DateTime $date) {
		$date->setTimezone(new DateTimeZone("UTC"));
		$this->setHeader("Date", $date->format("D, d M Y H:i:s") . " GMT");

		return $this;
	}

	/**
	 * Set the Expires HTTP header with a DateTime instance
	 *
	 * @param DateTime $date
	 *
	 * @return $this
	 */
	public function setHeaderExpires(DateTime $date) {
		$date->setTimezone(new DateTimeZone("UTC"));
		$this->setHeader("Expires", $date->format("D, d M Y H:i:s") . " GMT");

		return $this;
	}

	/**
	 * Set the Cache-Control HTTP header
	 *
	 * To set value requires sum of the weights necessary arguments
	 * Weight and values:
	 *  2   - public
	 *  4   - private
	 *  8   - no-cache
	 *  16  - no-store
	 *  32  - must-revalidate
	 *  64  - proxy-revalidate
	 *  128 - no-transform
	 *  256 - max-age=3600 (by default)
	 *  512 - s-maxage=600 (by default)
	 *
	 * @param int $bitWeight
	 * @param int $maxAge       TTL Cache-Control/max-age
	 * @param int $sharedMaxAge TTL Cache-Control/s-maxage
	 *
	 * @return $this
	 */
	public function setHeaderCacheControl($bitWeight = 0, $maxAge = 3600, $sharedMaxAge = 600) {
		$valuesByWeight = [
			2   => "public",
			4   => "private",
			8   => "no-cache",
			16  => "no-store",
			32  => "must-revalidate",
			64  => "proxy-revalidate",
			128 => "no-transform",
			256 => "max-age=" . $maxAge,
			512 => "s-maxage=" . $sharedMaxAge,
		];

		$header = [];
		foreach ($valuesByWeight as $weight => $attr) {
			if ($weight & $bitWeight) {
				$header[] = $attr;
			}
		}

		$this->setHeader("Cache-Control", implode(" ", array_reverse($header)));

		return $this;
	}

	/**
	 * Modifies the response so that it conforms to the rules defined for a 304 status code
	 *
	 * This sets the status, removes the body, and discards any headers
	 * that MUST NOT be included in 304 responses
	 *
	 * @return $this
	 */
	public function setHeaderNotModified() {
		$this->setStatus(Response::HTTP_NOT_MODIFIED);
		$this->setContent(null);

		//  remove headers that MUST NOT be included with 304 Not Modified responses
		foreach (["Allow", "Content-Encoding", "Content-Language", "Content-Length", "Content-MD5", "Content-Type", "Last-Modified"] as $header) {
			$this->deleteHeader($header);
		}

		return $this;
	}

	/**
	 * Determines if the Response validators (ETag, Last-Modified) match
	 * a conditional value specified in the Request
	 *
	 * @param Request $request
	 *
	 * @return bool
	 */
	public static function isNotModified(Request $request) {
		if (!$request->isMethod(Request::METHOD_GET) && !$request->isMethod(Request::METHOD_HEAD)) {
			return false;
		}

		$notModified = false;
		$lastModified = static::getHeader("Last-Modified");
		$modifiedSince = $request->getHeader("If-Modified-Since");

		if ($etags = $request->getHeader("If-None-Match")) {
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
	 * Return header by key or array of headers
	 *
	 * @param string $key
	 *
	 * @return array|mixed
	 * @throws NullPointException
	 */
	public function getHeader($key = "") {
		if ($key) {
			if (isset($this->headers[$key])) {
				return $this->headers[$key];
			}

			throw new NullPointException("Header with key '" . $key . "' not found");
		}

		return $this->headers;
	}

	/**
	 * Return true if header is defined
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasHeader($key) {
		return $this->headers[$key] ?? false;
	}

	/**
	 * Remove a header
	 *
	 * @param $key
	 *
	 * @return $this
	 */
	public function deleteHeader($key) {
		unset($this->headers[$key]);

		return $this;
	}

	/**
	 * Set the content
	 *
	 * Valid types are strings, numbers, null, and objects that implement a __toString() method
	 *
	 * @param string|null $body
	 *
	 * @return $this
	 * @throws UnexpectedValueException
	 */
	public function setContent($body) {
		if ($body === null || ($body && (is_string($body) || is_numeric($body))) || is_callable([$body, "__toString"])) {
			$this->setHeader("Content-Length", mb_strlen($body));
			$this->body = $body;

			return $this;
		}

		throw new UnexpectedValueException("The Response content must be a string or object implementing __toString(), " . gettype($body) . " given");
	}

	/**
	 * Return current content
	 *
	 * @return string Content
	 */
	public function getContent() {
		return $this->body;
	}

	/**
	 * Set status code
	 *
	 * @param int $code
	 *
	 * @return $this
	 * @throws InvalidArgumentException
	 */
	public function setStatus($code) {
		if ($code >= 100 || $code < 600) {
			$this->statusCode = $code;
			$this->statusText = static::$statusTexts[$code] ?? "unknown";

			return $this;
		}

		throw new InvalidArgumentException("The HTTP status code '" . $code . "'' is not valid");
	}

	/**
	 * Return status code
	 *
	 * @return int
	 */
	public function getStatus() {
		return $this->statusCode;
	}

	/**
	 * Set charset
	 *
	 * @param $charset
	 *
	 * @return $this
	 */
	public function setCharset($charset) {
		$this->charset = $charset;

		return $this;
	}

	/**
	 * Return charset
	 *
	 * @return string
	 */
	public function getCharset() {
		return $this->charset;
	}

	/**
	 * Is response informative?
	 *
	 * @return bool
	 */
	public function isInformational() {
		return $this->statusCode >= 100 && $this->statusCode < 200;
	}

	/**
	 * Is response successful?
	 *
	 * @return bool
	 */
	public function isSuccessful() {
		return $this->statusCode >= 200 && $this->statusCode < 300;
	}

	/**
	 * Is the response a redirect?
	 *
	 * @return bool
	 */
	public function isRedirection() {
		return $this->statusCode >= 300 && $this->statusCode < 400;
	}

	/**
	 * Is there a client error?
	 *
	 * @return bool
	 */
	public function isClientError() {
		return $this->statusCode >= 400 && $this->statusCode < 500;
	}

	/**
	 * Was there a server side error?
	 *
	 * @return bool
	 */
	public function isServerError() {
		return $this->statusCode >= 500 && $this->statusCode < 600;
	}

	/**
	 * Is the response OK?
	 *
	 * @return bool
	 */
	public function isOk() {
		return 200 === $this->statusCode;
	}

	/**
	 * Is the response forbidden?
	 *
	 * @return bool
	 */
	public function isForbidden() {
		return 403 === $this->statusCode;
	}

	/**
	 * Is the response a not found error?
	 *
	 * @return bool
	 */
	public function isNotFound() {
		return 404 === $this->statusCode;
	}

	/**
	 * Is the response a redirect of some form?
	 *
	 * @param string $location
	 *
	 * @return bool
	 */
	public function isRedirect($location = null) {
		return in_array($this->statusCode, [201, 301, 302, 303, 307, 308]) && (null === $location ? : $location == $this->headers["Location"]);
	}

	/**
	 * Is the response empty?
	 *
	 * @return bool
	 */
	public function isEmpty() {
		return in_array($this->statusCode, [204, 304]);
	}

	/**
	 * Send data to client
	 *
	 * @return $this
	 */
	public function send() {
		if (!headers_sent()) {
			static::sendHeaders();
			static::sendContent();
		}

		return $this;
	}

	/**
	 * Sends HTTP headers
	 */
	protected function sendHeaders() {
		// headers already sent
		if (headers_sent()) {
			return;
		}

		// date
		if (!$this->hasHeader("Date")) {
			$this->setHeaderDate(DateTime::createFromFormat("U", time()));
		}

		// headers
		foreach ($this->headers as $key => $value) {
			header($key . ": " . $value, false, $this->statusCode);
		}

		// status
		header("HTTP/1.1 " . $this->statusCode . " " . $this->statusText, true, $this->statusCode);
	}

	/**
	 * Print body content
	 */
	protected function sendContent() {
		echo $this->body;
	}

	/**
	 * Prepares Response before it is sent to the client
	 *
	 * @param Request $request
	 *
	 * @return $this
	 */
	public function prepare(Request $request) {
		if ($this->isInformational() || $this->isRedirection() || $this->isEmpty()) {
			$this->setContent(null);
			$this->deleteHeader("Content-Type");
			$this->deleteHeader("Content-Length");
		} else {
			// content-type based on the Request
			if (!$this->hasHeader("Content-Type")) {
				$mime = $request->getFormat();

				if (null !== $mime) {
					$this->setHeader("Content-Type", $mime . "; charset=" . $this->charset);
				}
			}

			// fix Content-Length
			if ($this->hasHeader("Transfer-Encoding")) {
				$this->deleteHeader("Content-Length");
			}

			if ($request->isMethod(Request::METHOD_HEAD)) {
				// RFC2616 14.13
				$length = $this->hasHeader("Content-Length");
				$this->setContent(null);
				if ($length) {
					$this->setHeader("Content-Length", $length);
				}
			}
		}

		if ($this->hasHeader("Transfer-Encoding")) {
			$this->deleteHeader("Content-Length");
		}

		return $this;
	}

	/**
	 * Return Response as an HTTP string
	 *
	 * @return string
	 */
	public function __toString() {
		$ret = "HTTP/1.1 " . $this->statusCode . " " . $this->statusText . "\r\n";

		foreach ($this->headers as $key => $value) {
			$ret .= $key . ": " . $value . "\r\n";
		}

		return $ret . "\r\n" . $this->getContent();
	}
}