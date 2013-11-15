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

		if(empty($this->ua)){
			return false;
		}

		//first detect ignore, if true, then return false

		//then detect match, if true, then return true

		//if no match at all, return false
		
		//empty user agents is also possible so return false

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

		if(file_exists($robots_json) AND is_readable($robots_json)){
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