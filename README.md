Snapsearch Client PHP
=====================

[![Build Status](https://travis-ci.org/SnapSearch/SnapSearch-Client-PHP.png?branch=master)](https://travis-ci.org/SnapSearch/SnapSearch-Client-PHP)

Snapsearch Client PHP is PHP based framework agnostic HTTP client library for SnapSearch (https://snapsearch.io/). It's PSR-0 compliant and can be integrated with [Stack PHP](http://stackphp.com/) or HTTP Kernel frameworks.

SnapSearch provides similar libraries in other languages: https://github.com/SnapSearch/Snapsearch-Clients

Installation
------------

Requires 5.3.3 or above and Curl extension.

**Composer**

Add this to your `composer.json`

```
"snapsearch/snapsearch-client-php": "~1.0.0"
```

Then run `composer install` or `composer update`.

**Native**

Just extract repository into your library location. Then use your own PSR-0 autoloader to autoload the classes inside `src/SnapSearchClientPHP/`.

You can also use the supplied autoloader. First clone this project to your desired location, then write:

```php
require_once('SnapSearch-Client-PHP/src/SnapSearchClientPHP/Bootstrap.php');
\SnapSearchClientPHP\Bootstrap::register();
```

If you don't want to use an autoloader, just require all the classes inside `src/SnapSearchClientPHP/` except `Bootstrap.php`.

Note you will have to install the dependencies and autoload them manually as well. Look into `composer.json` file and find the dependencies in the `"require"` section.

Don't forget about the `resources/` folder containing the necessary resources for this library to work.

Usage
-----

SnapSearchClientPHP should be best started at the entry point your application. This could be inside a front controller, bootstrapping process, IOC container, or middleware. For a single page application, your entry point would be the code that first presents the initial HTML page.

For full documentation on the API and API request parameters see: https://snapsearch.io/docs

**Basic Usage**

```php
$client = new \SnapSearchClientPHP\Client('email', 'key');
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
	
	//the complete $response['headers'] is not returned to the search engine due to potential content or transfer encoding issues, except for the potential location header, which is used when there is an HTTP redirect
	if(!empty($response['headers'])){
		foreach($response['headers'] as $header){
			if($header['name'] == 'Location'){
				header($header['name'] . ': ' . $header['value']);
			}
		}
	}

	//content
	echo $response['html'];

}else{

	//this request is not from a robot
	//continue with normal operations...

}
```

Here's an example `$response` variable (not all variables are available, you need to check the request parameters):

```php
$response = [
	'cache' 			=> true/false,
	'callbackResult'	=> '',
	'date'				=> 1390382314,
	'headers'			=> [
		[
			'name'	=> 'Content-Type',
			'value'	=> 'text/html'
		]
	],
	'html'				=> '<html></html>',
	'message'			=> 'Success/Failed/Validation Errors',
	'pageErrors'		=> [
		[
			error: 'Error: document.querySelector(...) is null',
			trace: [
				[
					'file': 'filename',
					'function': 'anonymous',
					'line': '41',
					'sourceURL': 'urltofile'
				]
			]
		]
	],
	'screensot'			=> 'BASE64 ENCODED IMAGE CONTENT',
	'status'			=> 200
]
```

**Advanced Usage**

```php
$request_parameters = array(
	//add your API request parameters if you have any...
);

$blacklisted_routes = array(
	//add your black listed routes if you have any...
);

$whitelisted_routes = array(
	//add your white listed routes if you have any...
);

$symfony_http_request_object = //get the Symfony\Component\HttpFoundation\Request

$robot_json_path = //if you have a custom Robots.json you can choose to use that instead, use the absolute path

$check_static_files = //if you wish for SnapSearchClient to check if the URL leads to a static file, switch this on to a boolean true, however this is expensive and time consuming, so it's better to use black listed or white listed routes

$client = new \SnapSearchClientPHP\Client('email', 'key', $request_parameters);

$detector = new \SnapSearchClientPHP\Detector(
	$blacklisted_routes, 
	$whitelisted_routes, 
	$symfony_http_request_object,
	$robot_json_path,
	$check_static_files
);

//robots can be direct accessed and manipulated
$detector->robots['match'][] = 'my_custom_bot_to_be_matched';

$interceptor = new \SnapSearchClientPHP\Interceptor($client, $detector);

//your custom cache driver
$cache = new YourCustomClientSideCacheDriver;

//this callback is called after the Detector has detected a search engine robot
//if this callback returns an array, the array will be used as the $response to $interceptor->intercept();
//use it for client side caching in order to have millisecond responses to search engines
$interceptor->before_intercept(function($url) use ($cache){

	//get cache from redis/filesystem..etc
	//returned value should array if successful or boolean false if cache did not exist
	return $cache->get($url); 
	
});

//this callback can be used to store the snapshot from SnapSearch as a client side cached resource
//this is of course optional as SnapSearch caches your snapshot as well!
$interceptor->after_intercept(function($url, $response) use ($cache){

	//the cached time should be less then the cached time you passed to SnapSearch, we recommend half the SnapSearch cachetime
	$time = '12hrs';
	$cache->store($url, $response, $time);
	
});

//exceptions should be ignored in production, but during development you can check it for validation errors
try{

	$response = $this->interceptor->intercept();

}catch(SnapSearchClientPHP\SnapSearchException $e){}

if($response){

	//this request is from a robot

	//status code
	header(' ', true, $response['status']); //as of PHP 5.4, you can use http_response_code($response['status']);
	
	//the complete $response['headers'] is not returned to the search engine due to potential content or transfer encoding issues, except for the potential location header, which is used when there is an HTTP redirect
	if(!empty($response['headers'])){
		foreach($response['headers'] as $header){
			if(strtolower($header['name']) == 'location'){
				header($header['name'] . ': ' . $header['value']);
			}
		}
	}
	
	//content
	echo $response['html'];

}else{

	//this request is not from a robot
	//continue with normal operations...

}
```

**Stack PHP Usage**

Stack PHP is a HTTP Kernel Middleware Layer Framework for PHP similar to Ruby Rack or Node Connect. The below example uses PHP 5.4 code.

```php
$app =  //HTTP Kernel controller

$stack = (new \Stack\Builder)->push(
	'\SnapSearchClientPHP\StackInterceptor',
	new Interceptor(
		new Client('email', 'key'), 
		new Detector
	)->before_intercept(function($url){
		//before interception callback (optional and chainable)
	})->after_intercept(function($url, $response){
		//after interception callback (optional and chainable)
	}),
	function(array $response){

		//this callback is completely optional, it allows you to customise your response
		//the $response array comes from SnapSearch and contains [(string) 'status', (array) 'headers', (string) 'html']

		return [
			'status'	=> $response['status'],
			'headers'	=> [
				//array of 'header_key' => 'header_value'
			],
			'html'		=> $response['html']
		];

	}
);

$app = $stack->resolve($app);

$request  = Request::createFromGlobals();
$response = $app->handle($request)->send();
$app->terminate($request, $response);
//or just do this if you have Stack\run
//\Stack\run($app);
```

The `$check_file_extensions` boolean for the Detector constructor is available for applications that might serve static files. Usually the HTTP server serves up static files and these requests never get proxied to the application, this is why by default this boolean is false. However in cases where it does serve up static files, you can switch this to true to prevent static files routes from being intercepted. 

It can be more efficient or easier to blacklist routes which lead to static files instead. This has the advantage of allowing you to prevent routes that go to binary resources which may not end in specific file extensions. Such as streaming audio/video.

SnapSearchClientPHP can of course be used in other areas such as javascript enhanced scraping, so it doesn't force you to put it at the entry point if you're using it for other purposes. In that case just use the `SnapSearchPHP\Client` to send requests to the SnapSearch API.

Proxies
-------

SnapSearch-Client-PHP uses the Symfony HTTP Foundation Request Object as an abstraction of the HTTP request. This allows you considerable flexibility and constructing the HTTP request especially when you're behind a reverse proxy such as a load balancer. If you are behind a reverse proxy, certain information such as the request protocol is not where it is normally. You can configure the Symfony HTTP Foundation Request Object to handle these edge cases, and simply pass your instance into the Detector. See this for more information: http://symfony.com/doc/current/components/http_foundation/trusting_proxies.html

Development
-----------

Install/update dependencies with composer:

```sh
composer update
```

Make your changes, synchronise, then create a new tag:

```sh
git tag MAJOR.MINOR.PATCH
git push
git push --tags
```

Packagist is integrated in the Github Service Hooks, it will automatically release the new package.

Tests
----

Unit tests are written using Codeception. Codeception has already been bootstrapped (`codecept bootstrap`). To run tests use `codecept run` or `codecept run --debug` for debug messages. If you change the Codeception configuration files or add extra functions to the helpers make sure to run `codecept build` so that the settings take effect.