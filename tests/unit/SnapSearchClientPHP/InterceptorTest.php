<?php

namespace SnapSearchClientPHP;

use Codeception\Util\Stub;

class InterceptorTest extends \Codeception\TestCase\Test{

	protected $interceptor;

	public function _before(){

		$detector = Stub::make('SnapSearchClientPHP\Detector', array(
			'detect'	=> function(){
				return true;
			},
			'get_encoded_url'	=> function(){
				return 'http://blah.com';
			}
		));

		$client = Stub::make('SnapSearchClientPHP\Client', array(
			'request'	=> function($url){
				return array(
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
				);
			}
		));

		$this->interceptor = new Interceptor($client, $detector);

	}

	public function testInterception(){

		$content = $this->interceptor->intercept();
		$this->assertInternalType('array', $content);

	}

}