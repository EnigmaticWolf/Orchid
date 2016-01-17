<?php

namespace Orchid;

class Response {
	public static $body    = "";
	public static $status  = 200;
	public static $mime    = "html";
	public static $nocache = false;
	public static $headers = [];

	protected static $mimeTypes = array(
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

		//Other
		"none"   => "text/plain",
	);

	public static function flush() {
		if (!headers_sent()) {
			if (static::$nocache || !empty($_SERVER["HTTPS"])) {
				header("Cache-Control: no-cache, must-revalidate");
				header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
				header("Pragma: no-cache");
			} else {
				header("Cache-Control: public, store, cache, no-validate");
				header("Expires: " . gmdate("D, d M Y H:i:s", (time() + (60 * 60 * 24))) . " GMT");
			}

			foreach (static::$headers as $key => $val) {
				header($key . ": " . $val);
			}

			http_response_code(static::$status);

			if (is_array(static::$body)) {
				static::$mime = "json";
				static::$body = json_encode(static::$body, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
			}
			if (!is_file(static::$body)) {
				header("Content-Type: " . static::$mimeTypes[static::$mime] . "; charset=utf-8");

				echo trim(static::$body);
			} else {
				header("Content-Type: " . static::$mimeTypes[@end(explode(".", basename(static::$body)))] . "; charset=utf-8");
				header("Content-Disposition: inline; filename='" . basename(static::$body) . "'");

				if (strpos($_SERVER['SERVER_SOFTWARE'], "nginx") !== false) {
					header("X-Accel-Redirect: " . str_replace(ORCHID, "", static::$body));
				} elseif (strpos($_SERVER['SERVER_SOFTWARE'], "Apache") !== false) {
					header("X-SendFile: " . str_replace(ORCHID, "", static::$body));
				} else {
					readfile(static::$body);
				}
			}
		}
	}
}