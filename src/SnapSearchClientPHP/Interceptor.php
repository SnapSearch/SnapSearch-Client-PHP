<?php

namespace SnapSearchClientPHP;

use SnapSearchClientPHP\Client;
use SnapSearchClientPHP\Detector;

/**
 * Interceptor intercepts the request and checks with the Detector if the request is valid for interception and then calls the Client for scraping and finally returns the content of the snapshot.
 */
class Interceptor{

	protected $detector;
	protected $client;
	protected $before;
	protected $after;

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
	 * Before intercept callback.
	 * This is intended for client side caching. It can request a client cached resource.
	 * However it can also be used for other purposes such as logging.
	 * If the callable returns an array, the array will be used as the returned response for Interceptor::intercept()
	 * The "callable" typehint is only available php > 5.4
	 * 
	 * @param callable $before Anonymous function to be executed before interception
	 */
	public function before_intercept($before){

		if(is_callable($before)){
			$this->before = $before;
		}

	}

	/**
	 * After intercept callback.
	 * This is intended for client side caching. It can store a SnapSearch response as a client cached resource.
	 * However it can also be used for other purposes such as logging.
	 * The callable should accept an array parameter which wil be the SnapSearch response
	 * The "callable" typehint is only available php > 5.4
	 * 
	 * @param callable $after Anonymous function to be executed after interception
	 */
	public function after_intercept($after){

		if(is_callable($after)){
			$this->after = $after;
		}

	}

	/**
	 * Intercept begins the detection and returns the snapshot if the request was scraped.
	 * 
	 * @return array|boolean
	 */
	public function intercept(){

		if($this->detector->detect()){

			//call the before interceptor and return an array response if it has one
			$before_intercept = $this->before;
			if($before_intercept){
				$result = $before_intercept();
				if(is_array($result)){
					return $result;
				}
			}

			$raw_current_url = $this->detector->get_encoded_url();

			$response = $this->client->request($raw_current_url);

			//call the after response interceptor, and pass in the $response array (which is always going to be an array)
			$after_intercept = $this->after;
			if($after_intercept){
				$after_intercept($response);
			}

			return $response;

		}

		return false;

	}

}