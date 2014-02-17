<?php

namespace SnapSearchClientPHP;

use Symfony\Component\HttpFoundation\Request;
 
class DetectorTest extends \Codeception\TestCase\Test{

	public function testNormalBrowserRequestShouldNotBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, null, false, $request);

		$this->assertFalse($detector->detect());

	}

	public function testSearchEngineRobotShouldBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, null, false, $request);

		$this->assertTrue($detector->detect());

	}

	public function testSnapSearchRobotShouldNotBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, null, false, $request);

		$this->assertFalse($detector->detect());

	}

	public function testNonGetRequestsShouldNotBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, null, false, $request);

		$this->assertFalse($detector->detect());

	}

	public function testIgnoredRoutesShouldNotBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(array('^\/ignored'), null, false, $request);

		$this->assertFalse($detector->detect());

	}

	public function testNonMatchedRoutesShouldNotBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, array('^\/non_matched_route'), false, $request);

		$this->assertFalse($detector->detect());

	}

	public function testMatchedRoutesShouldBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, array('^\/matched'), false, $request);

		$this->assertTrue($detector->detect());

	}

	public function testValidFileExtensionsShouldBeInterceptedIfOtherFactorsAllowIt(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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
				'REQUEST_URI' => '/snapsearch/song.html?key=value', 
			)
		);

		$detector = new Detector(null, null, true, $request);

		$this->assertTrue($detector->detect());

	}

	public function testInvalidFileExtensionsShouldNotBeIntercepted(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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
				'REQUEST_URI' => '/snapsearch/song.html.mp3?key=value', 
			)
		);

		$detector = new Detector(null, null, true, $request);

		$this->assertFalse($detector->detect());

	}

	public function testNonExistentFileExtensionShouldBeInterceptedIfOtherFactorsAllowIt(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, null, true, $request);

		$this->assertTrue($detector->detect());

	}

	public function testEscapedFragmentRouteShouldBeIntercepted(){

		$request = new Request(
			array(
				'_escaped_fragment_' => ''
			), 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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
				'QUERY_STRING' => '_escaped_fragment_',
				'REQUEST_URI' => '/snapsearch?_escaped_fragment_',
			)
		);

		$detector = new Detector(null, null, false, $request);

		$this->assertTrue($detector->detect());

	}

	public function testEscapedFragmentRouteShouldBeConvertedBackToHashFragment(){

		$request = new Request(
			array(
				'key1' => 'value1', 
				'_escaped_fragment_' => '/path2?key2=value2'
			), 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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
				'QUERY_STRING' => 'key1=value1&_escaped_fragment_=%2Fpath2%3Fkey2=value2',
				'REQUEST_URI' => '/snapsearch/path1?key1=value1&_escaped_fragment_=%2Fpath2%3Fkey2=value2',
			)
		);

		$detector = new Detector(null, null, false, $request);

		$this->assertEquals($detector->get_encoded_url(), 'http://localhost/snapsearch/path1?key1=value1#!/path2?key2=value2');

	}

	public function testSettingTheRobotsArray(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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

		$detector = new Detector(null, null, false, $request);

		$detector->robots['ignore'][] = 'Adsbot-Google';

		$this->assertFalse($detector->detect());

	}

	public function testSettingTheExtensionsArray(){

		$request = new Request(
			$_GET, 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			array(
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
				'REQUEST_URI' => '/snapsearch/song.html.mp3?key=value', 
			)
		);

		$detector = new Detector(null, null, true, $request);

		$detector->extensions['generic'][] = 'mp3';

		$this->assertTrue($detector->detect());

	}

}