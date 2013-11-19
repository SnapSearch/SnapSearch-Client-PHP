<?php

namespace SnapSearchClientPHP;

use Symfony\Component\HttpFoundation\Request;

class Detector{

	protected $request;
	protected $robots;
	protected $matched_routes;
	protected $ignored_routes;
	protected $check_static_files;

	public function __construct(
		Request $request = null,
		array $ignored_routes = null,
		array $matched_routes = null,
		$robots_json = false,
		$check_static_files = false
	){

		$this->request = ($request) ? $request : Request::createFromGlobals();
		$this->ignored_routes = ($ignored_routes) ? $ignored_routes : array();
		$this->matched_routes = ($matched_routes) ? $matched_routes : array();
		$robots_json = ($robots_json) ? $robots_json : dirname(__FILE__) . '/Robots.json';
		$this->robots = $this->parse_robots_json($robots_json);
		$this->check_static_files = (boolean) $check_static_files;

	}

	public function detect(){

		$user_agent = $this->request->headers->get('user-agent');
		$real_path = $this->get_decoded_path();
		$document_root = $this->request->server->get('DOCUMENT_ROOT');

		//only intercept on get requests, SnapSearch robot cannot submit a POST, PUT or DELETE request
		if($this->request->getMethod() != 'GET'){
			return false;
		}

		//let's not take any chances, empty user agents will not be intercepted
		if(empty($user_agent)){
			return false;
		}

		//only intercept on http or https protocols
		if($this->request->getScheme() !=  'http' AND $this->request->getScheme() != 'https'){
			return false;
		}

		//detect ignored user agents, if true, then return false
		foreach($this->robots['ignore'] as $key => $ignored_robot){
			$this->robots['ignore'][$key] = preg_quote($ignored_robot);
		}
		$ignore_regex = '/' . implode('|', $this->robots['ignore']) . '/i';
		if(preg_match($ignore_regex, $user_agent) === 1){
			return false;
		}

		//if the requested route doesn't match any of the whitelisted routes, then the request is ignored
		//of course this only runs if there are any routs on the whitelist
		if(!empty($this->matched_routes)){
			$matched_whitelist = false;
			foreach($this->matched_routes as $matched_route){
				$matched_route = '/' . $matched_route . '/i';
				if(preg_match($matched_route, $real_path) === 1){
					$matched_whitelist = true;
					break;
				}
			}
			if (!$matched_whitelist) return false;
		}

		//detect ignored routes
		foreach($this->ignored_routes as $ignored_route){
			$ignored_route = '/' . $ignored_route . '/i';
			if(preg_match($ignored_route, $real_path) === 1){
				return false;
			}
		}

		//ignore direct requests to files unless it's a php file
		if($this->check_static_files AND !empty($document_root) AND !empty($real_path)){

			//convert slashes to OS specific slashes
			//remove the trailing / or \ from the document root if it exists
			$document_root = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $document_root);
			$document_root = rtrim($document_root, DIRECTORY_SEPARATOR);

			//remove the leading / or \ from the path if it exists
			$real_path = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $real_path);
			$real_path = ltrim($real_path, DIRECTORY_SEPARATOR);

			$absolute_path = $document_root . DIRECTORY_SEPARATOR . $real_path;
			$path_info = pathinfo($absolute_path);
			$path_info['extension'] = (!empty($path_info['extension'])) ? $path_info['extension'] : '';

			if(is_file($absolute_path) AND $path_info['extension'] != 'php'){
				return false;
			}

		}

		//detect escaped fragment (since the ignored user agents has been already been detected, SnapSearch won't continue the interception loop)
		if($this->request->query->has('_escaped_fragment_')){
			return true;
		}

		//detect matched robots, if true, then return true
		foreach($this->robots['match'] as $key => $matched_robot){
			$this->robots['match'][$key] = preg_quote($matched_robot);
		}
		$match_regex = '/' . implode('|', $this->robots['match']) . '/i';
		if(preg_match($match_regex, $user_agent) === 1){
			return true;
		}

		//if no match at all, return false
		return false;

	}

	public function set_robots(array $robots, $type = false){

		if($type){
			if($type != 'ignore' OR $type != 'match'){
				return false;
			}
			$this->robots[$type] = $robots;
		}else{
			$this->robots = $robots;
		}

		return true;

	}

	public function add_match_robots($robots){

		if(is_array($robots)){
			$this->robots['match'] += $robots;
		}else{
			$this->robots['match'][] = $robots;
		}

	}

	public function add_ignore_robots($robots){

		if(is_array($robots)){
			$this->robots['ignore'] += $robots;
		}else{
			$this->robots['ignore'][] = $robots;
		}

	}

	//raw url is for the Robot
	//_escaped_fragment_ is converted back to hash fragment
	//ENCODED URL is the RAW URL
	//THE RAW URL IS FOR THE ROBOT
	public function get_encoded_url(){

		if($this->request->query->has('_escaped_fragment_')){

			$qs_and_hash = $this->get_real_qs_and_hash_fragment(true);

			$url = 
				$this->request->getSchemeAndHttpHost() 
				. $this->request->getBaseUrl() 
				. $this->request->getPathInfo() 
				. $qs_and_hash['qs'] 
				. $qs_and_hash['hash'];

			return $url;

		}else{

			return $this->request->getUri();

		}

	}

	protected function get_decoded_path(){

		if($this->request->query->has('_escaped_fragment_')){

			$qs_and_hash = $this->get_real_qs_and_hash_fragment(false);

			$path = 
				$this->request->getBaseUrl() 
				. $this->request->getPathInfo() 
				. $qs_and_hash['qs'] 
				. $qs_and_hash['hash'];

			return $path;

		}else{

			return rawurldecode($this->request->getRequestUri());

		}

	}

	/**
	 * Gets the real query string and hash fragment by reversing the Google's _escaped_fragment_ protocol
	 * to the hash bang mode.
	 * Google will convert the original url from:
	 * http://example.com/path#!key=value to http://example.com/path?_escaped_fragment_=key%26value
	 * Therefore we have to reverse this process to the original url which will be used for snapshotting purposes.
	 * https://developers.google.com/webmasters/ajax-crawling/docs/specification
	 * @param  Boolean $encode Whether to rawurlencode the query string or not
	 * @return Array           Array of query string and hash fragment
	 */
	protected function get_real_qs_and_hash_fragment($encode){

		$query_parameters = $this->request->query->all();
		unset($query_parameters['_escaped_fragment_']);

		$query_string = '';
		if(!empty($query_parameters)){
			if($encode){
				array_walk($query_parameters, function(&$value, $key){
					$value = rawurlencode($key) . '=' . rawurlencode($value);
				});
			}else{
				array_walk($query_parameters, function(&$value, $key){
					$value = $key . '=' . $value;
				});
			}
			$query_string = '?' . implode('&', $query_parameters);
		}

		$hash = $this->request->query->get('_escaped_fragment_');
		$hash_string = '';
		if(!empty($hash)){
			$hash_string = '#!' . $hash;
		}

		return array(
			'qs'	=> $query_string,
			'hash'	=> $hash_string
		);

	}

	protected function parse_robots_json($robots_json){

		if(is_file($robots_json) AND is_readable($robots_json)){
			$robots = file_get_contents($robots_json);
		}else{
			throw new \Exception('The robots json file could not be found or could not be read.');
		}

		$robots = json_decode($robots, true);

		switch(json_last_error()){
			case JSON_ERROR_DEPTH:
				$error = 'The robots json file exceeded maximum stack depth.';
			break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = 'The robots json file hit an underflow or the mods mismatched.';
				echo ' - Underflow or the modes mismatch';
			break;
			case JSON_ERROR_CTRL_CHAR:
				$error = 'The robots json file has an unexpected control character.';
			break;
			case JSON_ERROR_SYNTAX:
				$error = 'The robots json file has a syntax error, it\'s json is malformed.';
			break;
			case JSON_ERROR_UTF8:
				$error = 'The robots json file has malformed UTF-8 characters, possibly incorrectly encoded.';
			break;
			case JSON_ERROR_NONE:
			default:
				$error = '';
		}

		if(!empty($error)){
			throw new \Exception($error);
		}

		return $robots;

	}

}