<?php

namespace SnapSearchClientPHP;

use Codeception\Util\Stub;
use Codeception\Util\Debug;

use Symfony\Component\HttpFoundation\Request;
 
class DetectorTest extends \Codeception\TestCase\Test{

	protected $codeGuy;

	public $normal_browser = array ( 
		'_GET'		=> array(),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'GET', 
			'QUERY_STRING' => '', 
			'REQUEST_URI' => '/snapsearch/', 
		)
	);

	public $search_engine = array ( 
		'_GET'		=> array(),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'AdsBot-Google ( http://www.google.com/adsbot.html)', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'GET', 
			'QUERY_STRING' => '', 
			'REQUEST_URI' => '/snapsearch/', 
		)
	);

	public $snapsearch_robot = array ( 
		'_GET'	=> array(),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'SnapSearch', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'GET', 
			'QUERY_STRING' => '', 
			'REQUEST_URI' => '/snapsearch/', 
		)
	);

	public $non_get_route = array(
		'_GET'		=> array(),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'AdsBot-Google ( http://www.google.com/adsbot.html)', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'POST', 
			'QUERY_STRING' => '', 
			'REQUEST_URI' => '/snapsearch/', 
		)
	);

	public $ignored_route = array ( 
		'_GET'		=> array(),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'Googlebot-Video/1.0', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'GET', 
			'QUERY_STRING' => '', 
			'REQUEST_URI' => '/ignored/', 
		)
	);

	public $matched_route = array ( 
		'_GET'		=> array(),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'msnbot/1.1 ( http://search.msn.com/msnbot.htm)', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'GET', 
			'QUERY_STRING' => '', 
			'REQUEST_URI' => '/matched/', 
		)
	);

	public $escaped_fragment_route = array ( 
		'_GET'		=> array(
			'blah' => 'yay', 
			'_escaped_fragment_' => 'key1=lol'
		),
		'_SERVER'	=> array(
			'HTTP_HOST' => 'localhost', 
			'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:25.0) Gecko/20100101 Firefox/25.0', 
			'SERVER_NAME' => 'localhost', 
			'SERVER_PORT' => '80', 
			'REMOTE_ADDR' => '::1', 
			'DOCUMENT_ROOT' => 'C:/www', 
			'REQUEST_SCHEME' => 'http', 
			'GATEWAY_INTERFACE' => 'CGI/1.1', 
			'SERVER_PROTOCOL' => 'HTTP/1.1', 
			'REQUEST_METHOD' => 'GET', 
			'QUERY_STRING' => 'blah=yay&_escaped_fragment_=key1%3Dlol',
			'REQUEST_URI' => '/snapsearch/?blah=yay&_escaped_fragment_=key1%3Dlol',
		),
	);

	protected function _before(){

	}

	protected function _after(){

	}

	public function testNormalBrowserRequestShouldNotBeIntercepted(){

		$request = new Request(
			$this->normal_browser['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->normal_browser['_SERVER']
		);

		$detector = new Detector($request);

		$this->assertFalse($detector->detect());

	}

	public function testSearchEngineRobotShouldBeIntercepted(){

		$request = new Request(
			$this->search_engine['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->search_engine['_SERVER']
		);

		$detector = new Detector($request);

		$this->assertTrue($detector->detect());

	}

	public function testSnapSearchRobotShouldNotBeIntercepted(){

		$request = new Request(
			$this->snapsearch_robot['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->snapsearch_robot['_SERVER']
		);

		$detector = new Detector($request);

		$this->assertFalse($detector->detect());

	}

	public function testNonGetRequestsShouldNotBeIntercepted(){

		$request = new Request(
			$this->non_get_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->non_get_route['_SERVER']
		);

		$detector = new Detector($request);

		$this->assertFalse($detector->detect());

	}

	public function testIgnoredRoutesShouldNotBeIntercepted(){

		$request = new Request(
			$this->ignored_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->ignored_route['_SERVER']
		);

		$detector = new Detector($request, array('^\/ignored'));

		$this->assertFalse($detector->detect());

	}

	public function testNonMatchedRoutesShouldNotBeIntercepted(){

		$request = new Request(
			$this->matched_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->matched_route['_SERVER']
		);

		$detector = new Detector($request, null, array('^\/non_matched_route'));

		$this->assertFalse($detector->detect());

	}

	public function testMatchedRoutesShouldBeIntercepted(){

		$request = new Request(
			$this->matched_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->matched_route['_SERVER']
		);

		$detector = new Detector($request, null, array('^\/matched'));

		$this->assertTrue($detector->detect());

	}

	public function testEscapedFragmentRouteShouldBeIntercepted(){

		$request = new Request(
			$this->escaped_fragment_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->escaped_fragment_route['_SERVER']
		);

		$detector = new Detector($request);

		$this->assertTrue($detector->detect());

	}

	public function testEscapedFragmentRouteShouldBeConvertedBackToHashFragment(){

		$request = new Request(
			$this->escaped_fragment_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->escaped_fragment_route['_SERVER']
		);

		$detector = new Detector($request);

		$this->assertEquals($detector->get_encoded_url(), 'http://localhost/snapsearch/?blah=yay#!key1=lol');

	}

}