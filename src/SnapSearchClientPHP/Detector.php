<?php

namespace SnapSearchClientPHP;

use Symfony\Component\HttpFoundation\Request;

/**
 * Detector detects if the current request is from a search engine robot using the Robots.json file
 * Make sure to understand the difference between encoding and decoding via http://stackoverflow.com/a/21628649/582917
 */
class Detector{

	protected $ignored_routes;
	protected $matched_routes;
	protected $request;
	protected $check_static_files;
	protected $robots;

	/**
	 * Constructor
	 * 
	 * @param array   $ignored_routes     Array of blacklised route regexes that will be ignored during detection, you can use relative directory paths
	 * @param array   $matched_routes     Array of whitelisted route regexes, any route not matching will be ignored during detection
	 * @param Request $request            Symfony Request Object
	 * @param boolean $robots_json        Absolute path to a the Robots.json file
	 * @param boolean $check_static_files Switch to check if the path leads to a static file that is not a PHP script. This is prevent SnapSearch from attempting to scrape files that are not HTML. This is by default left off because it is an expensive check, and should be done only if you're hosting a lot of static files.
	 */
	public function __construct(
		array $ignored_routes = null,
		array $matched_routes = null,
		Request $request = null,
		$robots_json = false,
		$check_static_files = false
	){

		$this->ignored_routes = ($ignored_routes) ? $ignored_routes : array();
		$this->matched_routes = ($matched_routes) ? $matched_routes : array();
		$this->request = ($request) ? $request : Request::createFromGlobals();
		$robots_json = ($robots_json) ? $robots_json : dirname(__FILE__) . '/Robots.json';
		$this->robots = $this->parse_robots_json($robots_json);
		$this->check_static_files = (boolean) $check_static_files;

	}

	/**
	 * Detects if the request came from a search engine robot. It will intercept in cascading order:
	 * 1. on a GET request
	 * 2. on an HTTP or HTTPS protocol
	 * 3. not on any ignored robot user agents
	 * 4. not on any route not matching the whitelist
	 * 5. not on any route matching the blacklist
	 * 6. not on any static files that is not a PHP file if it is detected
	 * 7. on requests with _escaped_fragment_ query parameter
	 * 8. on any matched robot user agents
	 * 
	 * @return boolean
	 */
	public function detect(){

		//the user agent may not exist, so we want to make sure to gets typecast to a string
		$user_agent = (string) $this->request->headers->get('user-agent');
		$real_path = $this->get_decoded_path();
		$document_root = $this->request->server->get('DOCUMENT_ROOT');

		//only intercept on get requests, SnapSearch robot cannot submit a POST, PUT or DELETE request
		if($this->request->getMethod() != 'GET'){
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
		//of course this only runs if there are any routes on the whitelist
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

	/**
	 * Sets a matched or ignored robots array. This replaces the matched or ignored arrays in Robots.json
	 * 
	 * @param  array   $robots Array of robots user agents
	 * @param  boolean $type   Type can be 'ignore' or 'match'
	 *
	 * @return boolean
	 */
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

	/**
	 * Adds a single robot or an array of robots to the matched robots in Robots.json
	 * 
	 * @param string|array $robots String or array of robot user agents
	 */
	public function add_match_robots($robots){

		if(is_array($robots)){
			$this->robots['match'] += $robots;
		}else{
			$this->robots['match'][] = $robots;
		}

	}

	/**
	 * Adds a single robot or an array of robots to the ignored robots in Robots.json
	 * 
	 * @param string|array $robots String or array of robot user agents
	 */
	public function add_ignore_robots($robots){

		if(is_array($robots)){
			$this->robots['ignore'] += $robots;
		}else{
			$this->robots['ignore'][] = $robots;
		}

	}

	/**
	 * Gets the encoded URL that is passed to SnapSearch so that SnapSearch can scrape the encoded URL.
	 * If _escaped_fragment_ query parameter is used, this is converted back to a hash fragment URL.
	 * 
	 * @return string URL intended for SnapSearch
	 */
	public function get_encoded_url(){

		if($this->request->query->has('_escaped_fragment_')){

			$qs_and_hash = $this->get_real_qs_and_hash_fragment(true);

			//the query string must be ahead of the hash, because anything after hash is never passed to the server
			//and the server may require the query strings
			$url = 
				$this->request->getSchemeAndHttpHost() 
				. $this->request->getBaseUrl() 
				. $this->request->getPathInfo() 
				. $qs_and_hash['qs'] 
				. $qs_and_hash['hash'];

			return $url;

		}else{

			//gets the rawurlencoded complete uri
			return $this->request->getUri();

		}

	}

	/**
	 * Gets the decoded URL path relevant for detecting matched or ignored routes during detection.
	 * It is also used for static file detection. 
	 * 
	 * @return string
	 */
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

			//getRequestUri() gets the not urldecode() request path (not the full uri) and then runs rawurldecode to retrieve the literal form of the uri, so it can be used against the whitelist and blacklist regex
			//the regex is likely to contain literal forms of the urls, not encoded forms
			return rawurldecode($this->request->getRequestUri());

		}

	}

	/**
	 * Gets the real query string and hash fragment by reversing the Google's _escaped_fragment_ protocol to the hash bang mode. This is used for both getting the encoded url for scraping and the decoded path for detection.
	 * Google will convert convert URLs like so:
	 * Original URL: http://example.com/path1?key1=value1#!/path2?key2=value2
	 * Original Structure: DOMAIN - PATH - QS - HASH BANG - HASH PATH - HASH QS
	 * Search Engine URL: http://example.com/path1?key1=value1&_escaped_fragment_=%2Fpath2%3Fkey2=value2
	 * Search Engine Structure: DOMAIN - PATH - QS - ESCAPED FRAGMENT
	 * Everything after the hash bang will be stored as the _escaped_fragment_, even if they are query strings.
	 * Therefore we have to reverse this process to get the original url which will be used for snapshotting purposes.
	 * This means the original URL can have 2 query strings components.
	 * The QS before the HASH BANG will be received by both the server and the client. However not all client side frameworks will process this QS.
	 * The HASH QS will only be received by the client as anything after hash does not get sent to the server. Most client side frameworks will process this HASH QS.
	 * See this for more information: https://developers.google.com/webmasters/ajax-crawling/docs/specification
	 * 
	 * @param  boolean $encode Whether to rawurlencode the query string or not
	 * 
	 * @return array           Array of query string and hash fragment
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

		//all get parameters are automatically filtered via urldecode()
		$hash = $this->request->query->get('_escaped_fragment_');
		$hash_string = '';
		if(!empty($hash)){
			//the hash fragment can be anything, and the URL standard allows any characters after the hash, so no encoding required
			$hash_string = '#!' . $hash;
		}

		return array(
			'qs'	=> $query_string,
			'hash'	=> $hash_string
		);

	}

	/**
	 * Parses the Robots.json file by decoding the JSON and throwing an exception if the decoding went wrong.
	 * 
	 * @param  string $robots_json Absolute path to Robots.json
	 * 
	 * @return array
	 *
	 * @throws Exception If json decoding didn't work
	 */
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