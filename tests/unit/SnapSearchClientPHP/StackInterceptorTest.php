<?php

namespace SnapSearchClientPHP;

use Codeception\Util\Stub;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Stack\Builder;
use Stack\CallableHttpKernel;

use SnapSearchClientPHP\SnapSearchException;

class StackInterceptorTest extends \Codeception\TestCase\Test{

    protected $app;

    protected $response_array = array(
        'status'        => 200,
        'headers'       => array(
            array(
                'name'  => 'Location',
                'value' => 'http://somewhereelse.com'
            ),
            array(
                'name'  => 'Non existent',
                'value' => 'Non existent',
            )
        ),
        'html'          => '<html>Hi!</html>',
        'screenshot'    => '',
        'date'          => '324836',
        'cache'         => false
    );

    public function _before(){

        $response_array = $this->response_array;

        //core kernel, otherwise known as the controller
        $app = new CallableHttpKernel(function(Request $request){
            return new Response('did not get intercepted');
        });

        //interceptor is stubbed to always return a response
        $interceptor = Stub::make('SnapSearchClientPHP\Interceptor', array(
            'detector'  => Stub::make('SnapSearchClientPHP\Detector', array(
                'request'   => function(){
                    return true;
                }
            )),
            'intercept' => function() use ($response_array){
                return $response_array;
            }
        ));

        $stack = new Builder;
        $stack->push(
            'SnapSearchClientPHP\StackInterceptor', 
            $interceptor
        );

        $app = $stack->resolve($app);

        $this->app = $app;

    }

    public function testStackInterceptionReturnsResponseObject(){

        $request = Request::create('/');
        $response = $this->app->handle($request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);

    }

    public function testStackInterceptionReturnsStatus(){

        $request = Request::create('/');
        $response = $this->app->handle($request);
        $this->assertEquals($this->response_array['status'], $response->getStatusCode());

    }

    public function testStackInterceptionReturnsContent(){

        $request = Request::create('/');
        $response = $this->app->handle($request);
        $this->assertEquals($this->response_array['html'], $response->getContent());

    }

    public function testStackInterceptionReturnsLocationHeader(){

        $request = Request::create('/');
        $response = $this->app->handle($request);
        $this->assertTrue($response->headers->has('location'));

    }

    public function testStackInterceptionWithCustomResponseCallback(){

        $response_array = $this->response_array;

        //core kernel, otherwise known as the controller
        $app = new CallableHttpKernel(function(Request $request){
            return new Response('did not get intercepted');
        });

        //interceptor is stubbed to always return a response
        $interceptor = Stub::make('SnapSearchClientPHP\Interceptor', array(
            'detector'  => Stub::make('SnapSearchClientPHP\Detector', array(
                'request'   => function(){
                    return true;
                }
            )),
            'intercept' => function() use ($response_array){
                return $response_array;
            }
        ));

        $stack = new Builder;
        $stack->push(
            'SnapSearchClientPHP\StackInterceptor', 
            $interceptor,
            function($response){

                //pass through location header but nothing else
                $headers = array_filter($response['headers'], function($header){
                    if(strtolower($header['name']) == 'location'){
                        return true;
                    }
                    return false;
                });

                return array(
                    'status'    => 301,
                    'html'      => 'example',
                    'headers'   => $headers,
                );

            }
        );

        $app = $stack->resolve($app);

        $this->app = $app;

        $request = Request::create('/');
        $response = $this->app->handle($request);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('example', $response->getContent());
        $this->assertTrue($response->headers->has('Location'));
        $this->assertFalse($response->headers->has('Non existent'));

    }

    public function testStackInterceptionWithExceptionCallback(){

        $app = new CallableHttpKernel(function(Request $request){
            return new Response('did not get intercepted');
        });

        //the interceptor will now return a SnapSearchException
        $interceptor = Stub::make('SnapSearchClientPHP\Interceptor', array(
            'detector'  => Stub::make('SnapSearchClientPHP\Detector', array(
                'request'   => function(){
                    return true;
                }
            )),
            'intercept' => function(){
                throw new SnapSearchException('Oh no something went wrong!');
            }
        ));

        $stack = new Builder;

        //just making sure the callback was actually called, this will be late binded
        $was_this_called = false;

        //making tests compatible with php 5.3
        $self = $this;

        //snapsearch layer
        $stack->push(
            'SnapSearchClientPHP\StackInterceptor', 
            $interceptor,
            null,
            function($exception, $request) use (&$was_this_called, $self){

                //the SnapSearchException will be received here
                $self->assertInstanceOf('SnapSearchClientPHP\SnapSearchException', $exception);
                $self->assertEquals('Oh no something went wrong!', $exception->getMessage());

                //the request object will also be available if something failed
                $self->assertInstanceOf('Symfony\Component\HttpFoundation\Request', $request);

                $was_this_called = true;

            }
        );

        //random layer underneath that will be called because exceptions are ignored in production
        $stack->push(function($app){
            return new CallableHttpKernel(function(){
                return new Response('random layer', 200);
            });
        });

        $app = $stack->resolve($app);

        $this->app = $app;

        $request = Request::create('/');
        $response = $this->app->handle($request);

        $this->assertTrue($was_this_called);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('random layer', $response->getContent());

    }

}