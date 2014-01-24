<?php

namespace SnapSearchClientPHP;

/**
 * Bootstrap allows users to autoload SnapSearchClientPHP under PSR-0 rules.
 */
class Bootstrap{

	/**
	 * Registers the autoloader.
	 */
	public static function register(){

		spl_autoload_register(array('\SnapSearchClientPHP\Bootstrap', 'autoload'));

	}

	/**
	 * Autoloads the class.
	 * 
	 * @param  string $class Namespaced call to a particular class
	 */
	public static function autoload($class){

		//path to src folder, this only works with PSR-0
		$src_path = dirname(__DIR__) . DIRECTORY_SEPARATOR;

		//remove the first ns (\) since src_path already has it
 		$class = ltrim($class, '\\');
		$file  = '';
		$namespace = '';
 
		if($last_namespace_pos = strrpos($class, '\\')){
 
			$namespace = substr($class, 0, $last_namespace_pos);
			$class = substr($class, $last_namespace_pos + 1);
			//replace all backslashes with DIRECTORY_SEPARATOR, it adds one more to the end
			$file = strtr($namespace, '\\', DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
 
		}
		
		//replace all class names with (_) with DIRECTORY_SEPARATOR
		$file .= strtr($class, '_', DIRECTORY_SEPARATOR);
		 
		if(file_exists($src_path . $file . '.php')){
		
			require_once($src_path . $file . '.php');
			return;
 
		}

	}

}