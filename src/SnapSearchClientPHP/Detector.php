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
		$robots_json = false,
		array $matched_routes = null,
		array $ignored_routes = null,
		$check_static_files = false
	){

		//note that the user agent may be false
		$this->request = ($request) : Request::createFromGlobals();
		$robots_json = ($robots_json) ? $robots_json : './Robots.json';
		$this->robots = $this->parse_robots_json($robots_json);
		$this->matched_routes = ($matched_routes) ? $matched_routes : array();
		$this->ignored_routes = ($ignored_routes) ? $ignored_routes : array();
		$this->check_static_files = (boolean) $check_static_files;

		//we may need to url_decode the Document root and Request Uri


/*
		if(!empty($document_root)){
			$this->document_root = $document_root;
		}elseif(!empty($_SERVER['DOCUMENT_ROOT'])){
			$this->document_root = $_SERVER['DOCUMENT_ROOT'];
		}else{
			$this->document_root = false;
		}
		$this->document_root = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->document_root);

		if(!empty($request_uri)){
			$this->request_uri = $request_uri;
		}elseif(!empty($_SERVER['REQUEST_URI'])){
			$this->request_uri = $_SERVER['REQUEST_URI'];
		}else{
			$this->request_uri = false;
		}
		$this->request_uri = url_decode(str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $this->request_uri));
 */

	}

	public function detect(){

		//WE ALSO SHOULD match against the HTTP protocol or HTTPS protocol.

		//let's not take any chances, empty user agents will not be intercepted
		if(empty($this->ua)){
			return false;
		}

		foreach($this->robots['ignore'] as $key => $ignored_robot){
			$this->robots['ignore'][$key] = preg_quote($ignored_robot);
		}

		$ignore_regex = '/' . implode('|', $this->robots['ignore']) . '/i';

		//detect ignored user agents, if true, then return false
		if(preg_match($ignore_regex, $this->ua) === 1){
			return false;
		}

		//detect ignored routes
		foreach($this->ignored_routes as $ignored_route){
			$ignored_route = '/' . $ignored_route . '/i';
			if(preg_match($ignored_route, $this->request_uri) === 1){
				return false;
			}
		}

		//ignore direct requests to files unless it's a php file
		if($this->check_files AND !empty($this->document_root) AND !empty($this->request_uri)){

			//remove the trailing / or \ from the document root if it exists
			$this->document_root = rtrim($this->document_root, DIRECTORY_SEPARATOR);

			//remove the leading / or \ from request uri if it exists
			$this->request_uri = ltrim($this->request_uri, DIRECTORY_SEPARATOR);

			$absolute_path = $this->document_root . DIRECTORY_SEPARATOR . $this->request_uri;
			$path_info = pathinfo($absolute_path);

			if(
				is_file($absolute_path) 
				AND !empty($path_info['extension']) 
				AND $path_info['extension'] != 'php'
			){
				return false;
			}

		}

		//detect escaped fragment (since the ignored user agents has been already been detected, SnapSearch won't continue the interception loop)
		if(isset($_GET['_escaped_fragment_'])){
			return true;
		}

		foreach($this->robots['match'] as $key => $matched_robot){
			$this->robots['match'][$key] = preg_quote($matched_robot);
		}

		$match_regex = '/' . implode('|', $this->robots['match']) . '/i';

		//detect matched robots, if true, then return true
		if(preg_match($match_regex, $this->ua) === 1){
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

	public function get_url(){

		//we need to get the current url
		//this current url wont' be able to get the hash portion and hence hash bang
		//however if the meta exists, Google will send the hash bang portion to _escaped_fragment_
		//we'll need to reconstruct the url if that exists
		//the value of _escaped_fragment_ will be added to the scheme + host + port + path, then add all subsequent query parameters...
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