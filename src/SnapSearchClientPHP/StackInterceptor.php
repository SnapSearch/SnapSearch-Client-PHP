<?php

namespace SnapSearchClientPHP;

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use SnapSearchClientPHP\Interceptor;
use SnapSearchClientPHP\SnapSearchException;

/**
 * StackInterceptor integrates SnapSearchClientPHP into Stack PHP and HTTP Kernel frameworks
 */
class StackInterceptor implements HttpKernelInterface{

	/**
	 * HTTP Kernel Middleware
	 * 
	 * @var HTTPKernelInterface
	 */
	protected $app;

	protected $detector;

	/**
	 * Interceptor Object
	 * 
	 * @var Interceptor
	 */
	protected $interceptor;

	/**
	 * Custom response creation callback
	 * 
	 * @var callable
	 */
	protected $response_callback;

	/**
	 * Constructor
	 *
	 * This is intended to be used inside Stack PHP, for example:
	 * (new Stack\Builder)->push(
	 *     'SnapSearchClientPHP\StackInterceptor', 
	 *     new Interceptor(new Client('email', 'key'), new Detector)
	 * )->resolve();
	 * 
	 * @param HttpKernelInterface $app               The next HTTP Kernel middleware
	 * @param Interceptor         $interceptor       Interceptor instance
	 * @param boolean             $response_callback Callback that should accept a Response object
	 *                                               and return an array ['status', 'headers', 'html']
	 */
	public function __construct(HttpKernelInterface $app, Interceptor $interceptor, $response_callback = false){

		$this->app = $app;
		$this->interceptor = $interceptor;
		$this->response_callback = $response_callback;

	}

	/**
	 * Handles the request stack. It will only run on the master request.
	 * Exceptions will be caught by the kernel.
	 * If you passed in a response callback, it will be used to output a response to the client.
	 * 
	 * @param  Request $request Symfony Request Object
	 * @param  integer $type    Master or sub request
	 * @param  boolean $catch   Let the kernel catch the exceptions
	 * 
	 * @return Response
	 */
	public function handle(Request $request, $type = HttpKernelInterface::MASTER_REQUEST, $catch = true){

		if($type !== HttpKernelInterface::MASTER_REQUEST){
			return $this->app->handle($request, $type, $catch);
		}

		//replace the default request object with the middleware request object
		$this->interceptor->detector->request($request);

		$response = $this->interceptor->intercept();

		if($response){

			if(is_callable($this->response_callback)){
				
				$data = $this->response_callback($response);

				$html = (!empty($data['html'])) ? $data['html'] : '';
				$status = (!empty($data['status'])) ? $data['status'] : 200;
				$headers = (!empty($data['headers'])) ? $data['headers'] : array();

				return new Response($html, $status, $headers);

			}else{

				//to be safe, we only display any location headers, other headers will be up to the developer's discretion
				$headers = array();
				if(!empty($response['headers'])){
					foreach($response['headers'] as $header){
						if(strtolower($header['name']) == 'location'){
							$headers[$header['name']] = $header['value'];
						}
					}
				}

				return new Response($response['html'], $response['status'], $headers);

			}

		}

		return $this->app->handle($request, $type, $catch);

	}

}