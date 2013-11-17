<?php

namespace SnapSearchClientPHP;

use Detector;
use Client;
use Symfony\Component\HttpFoundation\Request;

class Interceptor{

	protected $detector;
	protected $client;
	protected $request;

	public function __construct(Client $client, Request $request = null, Detector $detector = null){

		$this->client = $client;
		$this->request = ($request) : Request::createFromGlobals();
		$this->detector = ($detector) : new Detector;

	}

	public function intercept(){

		if($this->detector->detect()){

			//we need to get the current url
			//this current url wont' be able to get the hash portion and hence hash bang
			//however if the meta exists, Google will send the hash bang portion to _escaped_fragment_
			//we'll need to reconstruct the url if that exists
			//the value of _escaped_fragment_ will be added to the scheme + host + port + path, then add all subsequent query parameters...


			return $this->client->request();
		}

		return false;

	}

}