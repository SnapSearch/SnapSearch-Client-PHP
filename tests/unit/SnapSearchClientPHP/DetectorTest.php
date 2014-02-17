<?php

namespace SnapSearchClientPHP;

use Symfony\Component\HttpFoundation\Request;
 
class DetectorTest extends \Codeception\TestCase\Test{

	protected $normal_browser = array( 
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

	protected $search_engine = array( 
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

	protected $snapsearch_robot = array( 
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

	protected $non_get_route = array(
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

	protected $ignored_route = array ( 
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

	protected $matched_route = array( 
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

	protected $invalid_file_extension = array(
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
			'REQUEST_URI' => '/snapsearch/song.html.mp3?key=value', 
		)
	);

	protected $valid_file_extension = array(
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
			'REQUEST_URI' => '/snapsearch/song.html?key=value', 
		)
	);

	protected $basic_escaped_fragment_route = array(
		'_GET'		=> array(
			'_escaped_fragment_' => ''
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
			'QUERY_STRING' => '_escaped_fragment_',
			'REQUEST_URI' => '/snapsearch?_escaped_fragment_',
		),
	);

	protected $escaped_fragment_route = array(
		'_GET'		=> array(
			'key1' => 'value1', 
			'_escaped_fragment_' => '/path2?key2=value2'
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
			'QUERY_STRING' => 'key1=value1&_escaped_fragment_=%2Fpath2%3Fkey2=value2',
			'REQUEST_URI' => '/snapsearch/path1?key1=value1&_escaped_fragment_=%2Fpath2%3Fkey2=value2',
		),
	);

	public function testNormalBrowserRequestShouldNotBeIntercepted(){

		$request = new Request(
			$this->normal_browser['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->normal_browser['_SERVER']
		);

		$detector = new Detector(null, null, false, $request);

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

		$detector = new Detector(null, null, false, $request);

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

		$detector = new Detector(null, null, false, $request);

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

		$detector = new Detector(null, null, false, $request);

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

		$detector = new Detector(array('^\/ignored'), null, false, $request);

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

		$detector = new Detector(null, array('^\/non_matched_route'), false, $request);

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

		$detector = new Detector(null, array('^\/matched'), false, $request);

		$this->assertTrue($detector->detect());

	}

	public function testValidFileExtensionsShouldBeInterceptedIfOtherFactorsAllowIt(){

		$request = new Request(
			$this->valid_file_extension['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->valid_file_extension['_SERVER']
		);

		$detector = new Detector(null, null, true, $request);

		$this->assertTrue($detector->detect());

	}

	public function testInvalidFileExtensionsShouldNotBeIntercepted(){

		$request = new Request(
			$this->invalid_file_extension['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->invalid_file_extension['_SERVER']
		);

		$detector = new Detector(null, null, true, $request);

		$this->assertFalse($detector->detect());

	}

	public function testNonExistentFileExtensionShouldBeInterceptedIfOtherFactorsAllowIt(){

		$request = new Request(
			$this->search_engine['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->search_engine['_SERVER']
		);

		$detector = new Detector(null, null, true, $request);

		$this->assertTrue($detector->detect());

	}

	public function testEscapedFragmentRouteShouldBeIntercepted(){

		$request = new Request(
			$this->basic_escaped_fragment_route['_GET'], 
			$_POST, 
			array(), 
			$_COOKIE, 
			$_FILES, 
			$this->basic_escaped_fragment_route['_SERVER']
		);

		$detector = new Detector(null, null, false, $request);

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