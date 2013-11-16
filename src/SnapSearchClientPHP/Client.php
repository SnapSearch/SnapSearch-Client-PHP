<?php

namespace SnapSearchClientPHP;

class Client{

	protected $api_key;
	protected $request_parameters;
	protected $api_url;

	public function __construct($api_key = false, array $request_parameters = null, $api_url = false){

		$this->api_key = $api_key;
		$this->request_parameters = ($request_parameters) ? $request_parameters : array();
		$this->api_url = ($api_url) ? $api_url : 'http://snapsearch.io/api/v1/robot';

	}
	
}