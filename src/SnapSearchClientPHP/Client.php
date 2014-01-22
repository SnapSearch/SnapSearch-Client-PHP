<?php

namespace SnapSearchClientPHP;

use Httpful\Request as Api;
use SnapSearchClientPHP\SnapSearchException;

/**
 * Client contacts SnapSearch and retrieves the snapshot
 */
class Client{

	protected $api_email;
	protected $api_key;
	protected $request_parameters;
	protected $api_url;
	protected $api;
	protected $errors;

	/**
	 * Constructor
	 * 
	 * @param string  $api_email          Email used for HTTP Basic
	 * @param string  $api_key            Key used for HTTP Basic
	 * @param array   $request_parameters Parameters passed to SnapSearch API
	 * @param boolean $api_url            Custom API Url
	 * @param Request $api                HTTP Request Library extending Httpful\Request
	 */
	public function __construct(
		$api_email, 
		$api_key,  
		array $request_parameters = null, 
		$api_url = false,
		Api $api = null
	){

		$this->api_email = $api_email;
		$this->api_key = $api_key;
		$this->request_parameters = ($request_parameters) ? $request_parameters : array();
		$this->api_url = ($api_url) ? $api_url : 'https://snapsearch.io/api/v1/robot';
		$this->api = ($api) ? $api : Api::init();

	}

	/**
	 * Sends a request to SnapSearch using the current url.
	 * 
	 * @param  string        $current_url Current URL that the Robot is going to be accessing
	 * 
	 * @return array|boolean Response array from SnapSearch or boolean false if there was an system error
	 * 
	 * @throws SnapSearchException If curl error
	 * @throws SnapsearchException If validation error
	 */
	public function request($current_url){

		//the current url must contain the entire url with the _escaped_fragment_ parsed out
		$this->request_parameters['url'] = $current_url;

		try{

			$response = $this->api
						->post($this->api_url)
						->authenticateWith($this->api_email, $this->api_key)
						->timeout(30)
						->body($this->request_parameters, 'json')
						->send()
						->raw_body;
		
		}catch(\Exception $e){

			throw new SnapSearchException('Could not establish a connection to SnapSearch.');

		}

		$response = json_decode($response, true);

		if($response['code'] == 'success'){

			//will return status, headers (array of name => value), html, screenshot, date
			return $response['content'];

		}elseif($response['code'] == 'validation_error'){

			//means that something was incorrect from the request parameters or the url could not be accessed
			throw new SnapSearchException('Validation error from SnapSearch. Check your request parameters.', $response['content']);

		}else{

			//system error on SnapSearch, nothing we can do
			return false;

		}
		
	}
	
}