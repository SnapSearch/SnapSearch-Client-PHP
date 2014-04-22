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
     * Custom exception callback
     * 
     * @var callable
     */
    protected $exception_callback;

    /**
     * Constructor
     *
     * This is intended to be used inside Stack PHP, for example:
     * (new Stack\Builder)->push(
     *     'SnapSearchClientPHP\StackInterceptor', 
     *     new Interceptor(new Client('email', 'key'), new Detector)
     * )->resolve();
     *
     * Any SnapSearchExceptions will be ignored, since we're assuming we're using this in production.
     * If you want to deal with those exceptions, just pass in an $exception_callback.
     * The reason being is that during production, exceptions are only possible if the remote server failed.
     * In that case, the entire application did not fail, as it is non critical.
     * Any validation exceptions should be caught during development.
     * 
     * @param HttpKernelInterface $app                The next HTTP Kernel middleware
     * @param Interceptor         $interceptor        Interceptor instance
     * @param callback            $response_callback  Callback that should accept a Response object
     *                                                and return an array ['status', 'headers', 'html']
     * @param callback            $exception_callback Callback that should be used if there was a SnapSearchException
     *                                                it should accept an exception and a request object
     */
    public function __construct(HttpKernelInterface $app, Interceptor $interceptor, $response_callback = null, $exception_callback = null){

        $this->app = $app;
        $this->interceptor = $interceptor;
        $this->response_callback = $response_callback;
        $this->exception_callback = $exception_callback;

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

        try{

            $response = $this->interceptor->intercept();

        }catch(SnapSearchException $exception){

            if(is_callable($this->exception_callback)){
                $exception_callback = $this->exception_callback;
                $exception_callback($exception, $request);
            }

            //just pass through to the next layer if there's an exception
            return $this->app->handle($request, $type, $catch);

        }

        if($response){

            if(is_callable($this->response_callback)){
                
                $response_callback = $this->response_callback;
                $response = $response_callback($response);

                $html = (!empty($response['html'])) ? $response['html'] : '';
                $status = (!empty($response['status'])) ? $response['status'] : 200;
                $headers = (!empty($response['headers'])) ? $response['headers'] : array();

                $headers_output = array();
                foreach($headers as $header){
                    $headers_output[$header['name']] = $header['value'];
                }

                return new Response($html, $status, $headers_output);

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