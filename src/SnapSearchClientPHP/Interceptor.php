<?php

namespace SnapSearchClientPHP;

use SnapSearchClientPHP\Detector;
use SnapSearchClientPHP\Client;

/**
 * Interceptor intercepts the request and checks with the Detector if the request is valid for interception and then calls the Client for scraping and finally returns the content of the snapshot.
 */
class Interceptor{

	protected $detector;
	protected $client;

	/**
	 * Constructor
	 * 
	 * @param Client   $client   Client object
	 * @param Detector $detector Detector object
	 */
	public function __construct(Client $client, Detector $detector){

		$this->client = $client;
		$this->detector = $detector;

	}

	/**
	 * Intercept begins the detection and returns the snapshot if the request was scraped.
	 * 
	 * @return array|boolean
	 */
	public function intercept(){

		if($this->detector->detect()){
			$raw_current_url = $this->detector->get_encoded_url();
			return $this->client->request($raw_current_url);
		}

		return false;

	}

}