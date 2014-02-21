<?php

namespace SnapSearchClientPHP;

use Codeception\Util\Stub;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Stack\Builder;
use Stack\CallableHttpKernel;

class StackInterceptorTest extends \Codeception\TestCase\Test{

    protected $app;

    protected $response_array = array(
        'status'        => 200,
        'headers'       => array(
            array(
                'name'  => 'Location',
                'value' => 'http://somewhereelse.com'
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
                return array(
                    'status'    => 301,
                    'html'      => 'example',
                    'headers'   => array(
                        'key'   => 'value'
                    ),
                );
            }
        );

        $app = $stack->resolve($app);

        $this->app = $app;

        $request = Request::create('/');
        $response = $this->app->handle($request);

        $this->assertEquals(301, $response->getStatusCode());
        $this->assertEquals('example', $response->getContent());
        $this->assertTrue($response->headers->has('key'));

    }

}