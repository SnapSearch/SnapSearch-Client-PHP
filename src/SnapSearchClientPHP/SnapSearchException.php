<?php

namespace SnapSearchClientPHP;

/**
 * SnapSearchException extends the Exception class to add extra functions to help with extracting the error strings.
 */
class SnapSearchException extends \Exception{

	/**
	 * Array of errors
	 * 
	 * @var array
	 */
	protected $errors_array = array();

	/**
	 * Constructor
	 * 
	 * @param string    $message  Error message
	 * @param array     $errors   Array of errors
	 * @param integer   $code     Exception Code
	 * @param Exception $previous Previous exception
	 */
	public function __construct($message = '', array $errors = array(), $code = 0, \Exception $previous = null){

		parent::__construct($message, $code, $previous);
		$this->errors_array = $errors;

	}

	/**
	 * Gets an array of all errors. It incorporates the basic single message error of most exceptions.
	 * This way you only have to use get_errors() regardless of whether it's multiple errors or a single error.
	 * 
	 * @return array Array of errors
	 */
	public function get_errors() {

		return $this->errors_array;

	}

	/**
	 * Gets a error string that is combined from the errors array.
	 * 
	 * @return string
	 */
	public function get_error_string(){

		return implode(' ', $this->errors_array);

	}

	/**
	 * Appends an error to the array of errors. This can be useful for multiple errors at the same time.
	 * 
	 * @param  string $error Message of the error
	 */
	public function append_error($error){

		$this->errors_array[] = $error;
	
	}

	/**
	 * Prepends an error to the array of errors.
	 * 
	 * @param  string $error Message of the error
	 */
	public function prepend_error($error){

		array_unshift($this->errors_array, $error);
	
	}

}