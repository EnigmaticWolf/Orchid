<?php

namespace Orchid;

use PHPUnit_Framework_TestCase;

class RouterTest extends PHPUnit_Framework_TestCase {
	public function testRouteUrl() {
		$this->assertEquals("http://domain.ru/", Router::routeUrl("/"));
		$this->assertEquals("http://domain.ru/test", Router::routeUrl("/test"));

		$this->assertEquals("http://other.domain.ru/", Router::routeUrl("/", "other"));
		$this->assertEquals("http://other.domain.ru/test", Router::routeUrl("/test", "other"));
	}
}
