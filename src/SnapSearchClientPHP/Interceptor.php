<?php

namespace SnapSearchClientPHP;

use UserAgentDetector;
use Client;

class Interceptor{

	protected $routes;
	protected $extensions;
	protected $request_parameters;

	public function __construct(UserAgentDetector $detector, Client $client){

		$this->detector = $detector;
		$this->client = $client;

	}

	public function intercept(){

		//should this echo the results?
		//intercept returns true
		//if intercept either returns true or the content
		//it's up to the developer to display the content and exit the app, or do further transformations

	}


}