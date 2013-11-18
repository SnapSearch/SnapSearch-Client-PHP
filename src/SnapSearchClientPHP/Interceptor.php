<?php

namespace SnapSearchClientPHP;

use Detector;
use Client;

class Interceptor{

	protected $detector;
	protected $client;

	public function __construct(Client $client, Detector $detector = null){

		$this->client = $client;
		$this->detector = ($detector) : new Detector;

	}

	public function intercept(){

		if($this->detector->detect()){
			$raw_current_url = $this->detector->get_encoded_url();
			return $this->client->request($raw_current_url);
		}

		return false;

	}

}