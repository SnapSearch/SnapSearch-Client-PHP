Snapsearch Client PHP Generic
=============================

[![Build Status](https://travis-ci.org/Polycademy/SnapSearch-Client-PHP.png?branch=master)](https://travis-ci.org/Polycademy/SnapSearch-Client-PHP)

Snapsearch Client PHP Generic is PHP based framework agnostic HTTP client library for SnapSearch (https://snapsearch.io/).

SnapSearch provides similar libraries in other languages: https://github.com/Polycademy/Snapsearch-Clients

Installation
------------

Requires 5.3.3 or above and Curl extension.

**Composer**

Add this to your `composer.json`

```
"polycademy/snapsearch-client-php": "~1.0.0"
```

Then run `composer install` or `composer update`.

**Native**

Just extract `src/SnapSearchClientPHP/` folder into your library location. Then use your own PSR-0 autoloader to autoload the classes.

You can also use the supplied autoloader through:

```
require_once('src/SnapSearchClientPHP/Bootstrap.php');
\SnapSearchClientPHP\Bootstrap::register();
```

If you don't want to use an autoloader, just require all the classes inside `src/SnapSearchClientPHP/` except `Bootstrap.php`.

Usage
-----

SnapSearchClientPHP should be best started at the entry point your application. This could be inside a front controller, bootstrapping process, IOC container, or middleware. For a single page application, your entry point would be the code that first presents the initial HTML page.

```php
$request_parameters = array(
	//add your API request parameters if you have any...
);

$client = new \SnapSearchClientPHP\Client('email', 'key', $request_parameters);
$detector = new \SnapSearchClientPHP\Detector;
$interceptor = new \SnapSearchClientPHP\Interceptor($client, $detector);

//exceptions should be ignored in production, but during development you can check it for validation errors
try{

	$response = $this->interceptor->intercept();

}catch(SnapSearchClientPHP\SnapSearchException $e){}

if($response){

	//this request is from a robot

	//status code
	header(' ', true, $response['status']); //as of PHP 5.4, you can use http_response_code($response['status']);
	
	//content
	echo $response['html'];

	//$response['headers'] is not returned to the search engine due to potential content or transfer encoding issuesm however you it is up to you if you want to test it out!

}else{

	//this request is not from a robot
	//continue with normal operations...

}
```

Here's an example `$response` variable (not all variables are available, you need to check the request parameters):

```php
$response = [
	'cache' 	=> 'true/false'
	'date'		=> 1390382314,
	'headers'	=> [
		[
			'name'	=> 'Content-Type',
			'value'	=> 'text/html'
		]
	],
	'html'		=> '<html></html>',
	'message'	=> 'Success/Failed/Validation Errors',
	'screensot'	=> 'BASE64 ENCODED IMAGE CONTENT',
	'status'	=> 200
]
```

SnapSearchClientPHP can of course be used in other areas such as js enhanced scraping, so it doesn't force you to put it at the entry point if you're using it for other purposes. In that case just use the `SnapSearchPHP\Client` to send requests to the SnapSearch API.

Tests
----

Unit tests are written using Codeception. Codeception has already been bootstrapped (`codecept bootstrap`). To run tests use `codecept run` or `codecept run --debug` for debug messages. If you change the Codeception configuration files or add extra functions to the helpers make sure to run `codecept build` so that the settings take effect.

Tests won't run on a 5.3 PHP system.