<?php

namespace Orchid\Extension;

use Orchid\App;
use PHPUnit_Framework_TestCase;

class AssetTest extends PHPUnit_Framework_TestCase {
	/**
	 * @param $resource
	 * @param $expected
	 *
	 * @dataProvider providerRender
	 */
	public function testRender($resource, $expected) {
		Asset::$map = $resource;

		$this->assertEquals($expected, Asset::render());
	}

	public function providerRender() {
		return [
			// без использования фильтров
			[["/js/script.js"], '<script type="text/javascript" src="/js/script.js"></script>'],
			[["/css/style.css"], '<link rel="stylesheet" type="text/css" href="/css/style.css" />'],
			[["/less/style.less"], '<link rel="stylesheet/less" type="text/css" href="/less/style.less" />'],

			// фильтр общих ресурсов
			[["*" => ["/js/script.js"]], '<script type="text/javascript" src="/js/script.js"></script>'],
			[["*" => ["/css/style.css"]], '<link rel="stylesheet" type="text/css" href="/css/style.css" />'],
			[["*" => ["/less/style.less"]], '<link rel="stylesheet/less" type="text/css" href="/less/style.less" />'],

			// фильтр контроллера
			[["foo/*" => ["/js/script.js"]], '<script type="text/javascript" src="/js/script.js"></script>'],
			[["foo/*" => ["/css/style.css"]], '<link rel="stylesheet" type="text/css" href="/css/style.css" />'],
			[["foo/*" => ["/less/style.less"]], '<link rel="stylesheet/less" type="text/css" href="/less/style.less" />'],

			// фильтр страницы
			[["foo/bar" => ["/js/script.js"]], '<script type="text/javascript" src="/js/script.js"></script>'],
			[["foo/bar" => ["/css/style.css"]], '<link rel="stylesheet" type="text/css" href="/css/style.css" />'],
			[["foo/bar" => ["/less/style.less"]], '<link rel="stylesheet/less" type="text/css" href="/less/style.less" />'],

			// смешанная карта ресурсов
			[
				[
					"/css/style.css",
					"*"       => ["/js/script.js"],
					"bar/*"   => ["/js/foo/script.js"],
					"foo/*"   => ["/js/foo/script.js"],
					"foo/bar" => ["/js/foo/bar_script.js"],
					"foo/qwe" => ["/js/foo/bar_script.js"],
				],
				'<script type="text/javascript" src="/js/script.js"></script>' . "\n" .
				'<script type="text/javascript" src="/js/foo/script.js"></script>' . "\n" .
				'<script type="text/javascript" src="/js/foo/bar_script.js"></script>',
			],
		];
	}

	public function testTemplate() {
		App::addPath("template", ORCHID . "/storage/test/template");

		$expected = '<script id="tpl-php-template-1" type="text/template">hello world</script>' . "\n" .
			'<script id="tpl-js-template-2" type="text/template">hello world</script>';

		$this->assertEquals($expected, Asset::template());
	}
}
