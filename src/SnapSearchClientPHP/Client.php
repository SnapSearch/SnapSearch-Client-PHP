<?php

namespace SnapSearchClientPHP;

use Httpful\Request as Api;
use SnapSearchException;

class Client{

	protected $api_key;
	protected $request_parameters;
	protected $api_url;
	protected $api;
	protected $errors;

	public function __construct(
		$api_key, 
		array $request_parameters = null, 
		$api_url = false,
		Api $api = null
	){

		$this->api_key = $api_key;
		$this->request_parameters = ($request_parameters) ? $request_parameters : array();
		$this->api_url = ($api_url) ? $api_url : 'http://snapsearch.io/api/v1/robot';
		$this->api = ($api) ? $api : Api::init();

	}

	public function request($current_url){

		//the current url must contain the entire url with the _escaped_fragment_ parsed out
		$this->request_parameters['url'] = $current_url;

		try{

			$response = $this->api
						->post($this->api_url)
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