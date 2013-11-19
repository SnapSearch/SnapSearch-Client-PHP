<?php

namespace SnapSearchClientPHP;

use SnapSearchClientPHP\Detector;
use SnapSearchClientPHP\Client;

class Interceptor{

	protected $detector;
	protected $client;

	public function __construct(Client $client, Detector $detector){

		$this->client = $client;
		$this->detector = $detector;

	}

	public function intercept(){

		if($this->detector->detect()){
			$raw_current_url = $this->detector->get_encoded_url();
			return $this->client->request($raw_current_url);
		}

		return false;

	}

}