<?php

namespace SnapSearchClientPHP;

class UserAgentDetector{

	protected $ua;
	protected $robots;

	public function __construct($ua = false, $robots_json = false){

		if(!empty($ua)){
			$this->ua = $ua;
		}elseif(!empty($_SERVER['HTTP_USER_AGENT'])){
			$this->ua = $_SERVER['HTTP_USER_AGENT'];
		}else{
			$this->ua = '';
		}

		$robots_json = ($robots_json) ? $robots_json : 'Robots.json';
		$this->robots = $this->parse_robots_json($robots_json);

	}

	public function detect(){

		//empty user agents is also possible so return false
		if(empty($this->ua)){
			return false;
		}

		foreach($this->robots['ignore'] as $key => $ignored_robot){
			$this->robots['ignore'][$key] = preg_quote($ignored_robot);
		}

		foreach($this->robots['match'] as $key => $matched_robot){
			$this->robots['match'][$key] = preg_quote($matched_robot);
		}

		$ignore_regex = '/' . implode('|', $this->robots['ignore']) . '/i';
		$match_regex = '/' . implode('|', $this->robots['match']) . '/i';

		//first detect ignore, if true, then return false
		if(preg_match($ignore_regex, $this->ua) === 1){
			return false;
		}

		//then detect match, if true, then return true
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