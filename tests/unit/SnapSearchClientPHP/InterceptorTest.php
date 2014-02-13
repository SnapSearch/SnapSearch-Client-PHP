<?php

namespace SnapSearchClientPHP;

use Codeception\Util\Stub;

class InterceptorTest extends \Codeception\TestCase\Test{

	protected $interceptor;

	protected $response_array = array(
		'status'		=> 200,
		'headers'		=> array(
			array(
				'name'	=> 'Date',
				'value'	=> 'Tue, 19 Nov 2013 18:23:41 GMT'
			)
		),
		'html'			=> '<html>Hi!</html>',
		'screenshot'	=> '',
		'date'			=> '324836',
		'cache'			=> false
	);

	public function _before(){

		$response_array = $this->response_array;

		$detector = Stub::make('SnapSearchClientPHP\Detector', array(
			'detect'	=> function(){
				return true;
			},
			'get_encoded_url'	=> function(){
				return 'http://blah.com';
			}
		));

		$client = Stub::make('SnapSearchClientPHP\Client', array(
			'request'	=> function($url) use ($response_array){
				return $response_array;
			}
		));

		$this->interceptor = new Interceptor($client, $detector);

	}

	public function testBeforeInterceptCallableThatReturnsAnArrayWillBeTheResponseToInterception(){

		$response_array = array(
			'test' => 'value'
		);

		$this->interceptor->before_intercept(function() use ($response_array){
			return $response_array;
		});

		$content = $this->interceptor->intercept();

		$this->assertEquals($content, $response_array);

	}

	public function testBeforeInterceptCallableThatDoesNotReturnAnArrayWillNotBeTheResponseToInterception(){

		$response_string = 'i will not be the response!';

		$this->interceptor->before_intercept(function() use ($response_string){
			return $response_string;
		});

		$content = $this->interceptor->intercept();

		$this->assertNotEquals($content, $response_string);

	}

	public function testAfterInterceptCallableShouldReceiveResponseArray(){

		$after_intercept_response_array = false;

		//late binding so it's by reference
		$this->interceptor->after_intercept(function($response_array) use (&$after_intercept_response_array){
			$after_intercept_response_array = $response_array;
		});

		$content = $this->interceptor->intercept();

		$this->assertEquals($after_intercept_response_array, $content);
		$this->assertEquals($after_intercept_response_array, $this->response_array);

	}

	public function testAfterInterceptCallableDoesNotNeedToAcceptParameters(){

		$after_intercept = false;

		//late binding so it's by reference
		$this->interceptor->after_intercept(function() use (&$after_intercept){
			$after_intercept = 'whateveriwant';
		});

		$this->interceptor->intercept();

		$this->assertEquals($after_intercept, 'whateveriwant');

	}

	public function testBeforeAndEmptyInterceptCallablesHaveToBeCallablesToBeCalled(){

		$this->interceptor->before_intercept('not a callable');
		$this->interceptor->after_intercept('not a callable');

		$content = $this->interceptor->intercept();

		$this->assertInternalType('array', $content);
		$this->assertEquals($content, $this->response_array);

	}

	public function testInterception(){

		$content = $this->interceptor->intercept();

		$this->assertInternalType('array', $content);
		$this->assertEquals($content, $this->response_array);

	}

}