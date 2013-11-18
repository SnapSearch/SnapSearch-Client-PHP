<?php

namespace SnapSearchClientPHP;

//this allows the creation of mocks and stubs
use Codeception\Util\Stub;
//this allows logging to the console
use Codeception\Util\Debug;

use Symfony\Component\HttpFoundation\Request;

class DetectorTest extends \Codeception\TestCase\Test{

	protected $codeGuy;

	protected $detector;

	protected function _before(){

		

		//get the basic server parameters and simulate a request from a search engine!
		

		$get = array(

		);

		$server = array(

		);

		//let's establish the request object first
		$request = new Request($_GET, $_POST, array(), $_COOKIE, $_FILES, $_SERVER);


	}

	protected function _after(){

	}

	public function testMe(){

		Debug::debug(print_r($_SERVER, true));
		//Debug::debug('Hi fdgoifiogsd hfsghgf hfghfg hfgdhfgdhfgd hfdhfdghg');

	}

}