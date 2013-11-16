<?php

namespace SnapSearchClientPHP;

use Detector;
use Client;

class Interceptor{

	protected $routes;
	protected $extensions;
	protected $request_parameters;

	public function __construct(Detector $detector, Client $client){

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