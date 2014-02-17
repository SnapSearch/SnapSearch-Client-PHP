<?php

namespace SnapSearchClientPHP;

use Symfony\Component\HttpFoundation\Request;
use SnapSearchClientPHP\SnapSearchException;

/**
 * Detector detects if the current request is from a search engine robot using the Robots.json file
 * Make sure to understand the difference between encoding and decoding via http://stackoverflow.com/a/21628649/582917
 */
class Detector{

	protected $ignored_routes;
	protected $matched_routes;
	protected $request;
	protected $check_file_extensions;
	protected $robots;
	protected $extensions;

	/**
	 * Constructor
	 * 
	 * @param array   $ignored_routes        Array of blacklised route regexes that will be ignored during detection, you can use relative directory paths
	 * @param array   $matched_routes        Array of whitelisted route regexes, any route not matching will be ignored during detection
	 * @param boolean $check_file_extensions Boolean to check if the url is going to a static file resource that should not be intercepted. This is prevent SnapSearch from attempting to scrape files which are not HTML. This is false by default as it depends on your routing structure.
	 * @param Request $request               Symfony Request Object
	 * @param string  $robots_json           Absolute path to a Robots.json file
	 * @param string  $extensions_json       Absolute path to a Extensions.json file
	 */
	public function __construct(
		array $ignored_routes = null,
		array $matched_routes = null,
		$check_file_extensions = false,
		Request $request = null,
		$robots_json = false,
		$extensions_json = false
	){

		$this->ignored_routes = ($ignored_routes) ? $ignored_routes : array();
		$this->matched_routes = ($matched_routes) ? $matched_routes : array();
		$this->check_file_extensions = (boolean) $check_file_extensions;
		$this->request = ($request) ? $request : Request::createFromGlobals();
		$robots_json = ($robots_json) ? $robots_json : __DIR__ . '/../../resources/robots.json';
		$this->robots = $this->parse_json($robots_json);
		$extensions_json = ($extensions_json) ? $extensions_json : __DIR__ . '/../../resources/extensions.json';
		$this->extensions = $this->parse_json($extensions_json);

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
		$ignore_regex = '/' . implode('|', $this->robots['ignore']) . '/iu';
		if(preg_match($ignore_regex, $user_agent) === 1){
			return false;
		}

		//if the requested route doesn't match any of the whitelisted routes, then the request is ignored
		//of course this only runs if there are any routes on the whitelist
		if(!empty($this->matched_routes)){
			$matched_whitelist = false;
			foreach($this->matched_routes as $matched_route){
				$matched_route = '/' . $matched_route . '/iu';
				if(preg_match($matched_route, $real_path) === 1){
					$matched_whitelist = true;
					break;
				}
			}
			if (!$matched_whitelist) return false;
		}

		//detect ignored routes
		foreach($this->ignored_routes as $ignored_route){
			$ignored_route = '/' . $ignored_route . '/iu';
			if(preg_match($ignored_route, $real_path) === 1){
				return false;
			}
		}

		//detect extensions in order to prevent direct requests to static files
		if($this->check_file_extensions){

			//create an array of extensions that are common for HTML resources
			$generic_extensions = (
				!empty($this->extensions['generic']) 
				AND 
				is_array($this->extensions['generic'])
			) ? $this->extensions['generic'] : array();

			$php_extensions = (
				!empty($this->extensions['php']) 
				AND 
				is_array($this->extensions['generic'])
			) ? $this->extensions['php'] : array();

			$valid_extensions = array_unique(
				array_merge(
					$generic_extensions, 
					$php_extensions
				)
			);

			//regex for url file extensions, it looks for "/{file}.{extension}" in an url that is not preceded by ? (query parameters) or # (hash fragment)
			$extension_regex = '~
				^              # Regex begins at the beginning of the string
				(?:            # Begin non-capturing group
					(?!        # Negative lookahead, this presence of such a sequence will fail the regex
					   [?#]    # Question mark or hash character
					   .*      # Any or more wildcard characters
					   /       # Literal slash
					   [^/?#]+ # {file} - has one or more of any character except forward slash, question mark or hash
					   \.      # Literal dot
					   [^/?#]+ # {extension} - has one or more of any character except forward slash, question mark or hash
					)          # This negative lookahead prevents any ? or # that precedes the {file}.{extension} by any characters
					.          # Wildcard
				)*             # Non-capturing group that will capture any number of wildcard that passes the negative lookahead
				/              # Literal slash
				[^/?#]+        # {file} - has one or more of any character except forward slash, question mark or hash
				\.             # Literal dot
				([^/?#]+)      # {extension} - Subgroup has one or more of any character except forward slash, question mark or hash
			~ux';

			//extension regex will be tested against the decoded path, not the full url to avoid domain extensions
			foreach($valid_extensions as $extension){
				//if there were no extensions found, then it's a pass
				if(preg_match($extension_regex, $real_path, $matches) === 1){
					//found an extension, check if it equals one of the valid HTML resource extensions
					if($extension != $matches[1]){
						return false;
					}
				}
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
		$match_regex = '/' . implode('|', $this->robots['match']) . '/iu';
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
	 */
	public function set_robots(array $robots, $type = false){

		if($type){
			if($type != 'ignore' OR $type != 'match'){
				throw new SnapSearchException('Robots array need to be set with a type equal to "ignore" or "match".');
			}
			$this->robots[$type] = $robots;
		}else{
			$this->robots = $robots;
		}

	}

	/**
	 * Sets valid URL file extensions. This can replace the extensions in Extensions.json or add/replace a certain type of extensions.
	 * 
	 * @param array   $extensions Array of extensions
	 * @param boolean $type       Type can be any string such as "php" or "generic"
	 */
	public function set_extensions(array $extensions, $type = false){

		if($type){
			$this->extension[$type] = $extensions;
		}else{
			$this->extensions = $extensions;
		}

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
	 * Adds a single extension or an array of extensions to the PHP type in Extensions.json
	 * 
	 * @param string|array $extensions String or array of extensions
	 */
	public function add_extensions($extensions){

		if(is_array($extensions)){
			$this->extensions['php'] += $extensions;
		}else{
			$this->extensions['php'][] = $extensions;
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
	 * Parses json files by decoding the JSON and throwing an exception if the decoding went wrong.
	 * 
	 * @param  string $json_file Absolute path to JSON file
	 * 
	 * @return array
	 *
	 * @throws Exception If json decoding didn't work
	 */
	protected function parse_json($json_file){

		if(is_file($json_file) AND is_readable($json_file)){
			$data = file_get_contents($json_file);
		}else{
			throw new \Exception("The $json_file file could not be found or could not be read.");
		}

		$data = json_decode($data, true);

		switch(json_last_error()){
			case JSON_ERROR_DEPTH:
				$error = "The $json_file file exceeded maximum stack depth.";
			break;
			case JSON_ERROR_STATE_MISMATCH:
				$error = "The $json_file file hit an underflow or the mods mismatched.";
			break;
			case JSON_ERROR_CTRL_CHAR:
				$error = "The $json_file file has an unexpected control character.";
			break;
			case JSON_ERROR_SYNTAX:
				$error = "The $json_file file has a syntax error, it\'s JSON is malformed.";
			break;
			case JSON_ERROR_UTF8:
				$error = "The $json_file file has malformed UTF-8 characters, it could be incorrectly encoded.";
			break;
			case JSON_ERROR_NONE:
			default:
				$error = '';
		}

		if(!empty($error)){
			throw new \Exception($error);
		}

		return $data;

	}

}