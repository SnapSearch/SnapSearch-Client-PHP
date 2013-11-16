<?php

namespace SnapSearchClientPHP;

use Detector;
use Client;

class Interceptor{

	public function __construct(Detector $detector, Client $client){

		$this->detector = ($detector) : new Detector;
		$this->client = ($client) : new Client;

	}

	public function intercept(){

		if($this->detector->detect()){
			return $this->client->request();
		}

		return false;

	}

}