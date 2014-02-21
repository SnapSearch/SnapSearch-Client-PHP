<?php

namespace SnapSearchClientPHP;

use SnapSearchClientPHP\Client;
use SnapSearchClientPHP\Detector;

/**
 * Interceptor intercepts the request and checks with the Detector if the request is valid for interception and then calls the Client for scraping and finally returns the content of the snapshot.
 */
class Interceptor{

	/**
	 * Client
	 * 
	 * @var Client
	 */
	public $client;

	/**
	 * Detector
	 * 
	 * @var Detector
	 */
	public $detector;

	/**
	 * Before interception callback
	 * 
	 * @var callable
	 */
	protected $before;

	/**
	 * After interception callback
	 * 
	 * @var callable
	 */
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
	 * This is intended for client side caching. It can be used for requesting a client cached resource.
	 * However it can also be used for other purposes such as logging.
	 * The callable should accept a string parameter which will the current URL that is being requested.
	 * If the callable returns an array, the array will be used as the returned response for Interceptor::intercept()
	 * The "callable" typehint is only available php > 5.4
	 * 
	 * @param  callable    $before Anonymous function to be executed before interception
	 *
	 * @return Interceptor $this
	 */
	public function before_intercept($before){

		if(is_callable($before)){
			$this->before = $before;
		}

		return $this;

	}

	/**
	 * After intercept callback.
	 * This is intended for client side caching or as an alternative way to respond to interception when integrated into middleware stacks.
	 * However it can also be used for other purposes such as logging.
	 * The callable should accept a string parameter and array parameter which will be respectively the current url being requested, and the snapshot response. 
	 * The "callable" typehint is only available php > 5.4
	 * 
	 * @param  callable    $after Anonymous function to be executed after interception
	 *
	 * @return Interceptor $this
	 */
	public function after_intercept($after){

		if(is_callable($after)){
			$this->after = $after;
		}

		return $this;

	}

	/**
	 * Intercept begins the detection and returns the snapshot if the request was scraped.
	 * 
	 * @return array|boolean
	 */
	public function intercept(){

		if($this->detector->detect()){

			$raw_current_url = $this->detector->get_encoded_url();

			//call the before interceptor and return an array response if it has one
			$before_intercept = $this->before;
			if($before_intercept){
				$result = $before_intercept($raw_current_url);
				if(is_array($result)){
					return $result;
				}
			}

			$response = $this->client->request($raw_current_url);

			//call the after response interceptor, and pass in the $response array (which is always going to be an array)
			$after_intercept = $this->after;
			if($after_intercept){
				$after_intercept($raw_current_url, $response);
			}

			return $response;

		}

		return false;

	}

}