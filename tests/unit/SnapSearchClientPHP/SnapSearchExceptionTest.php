<?php

namespace SnapSearchClientPHP;

class SnapSearchExceptionTest extends \Codeception\TestCase\Test{

    protected $snapsearch_exception;

    public function _before(){

        $this->snapsearch_exception = new SnapSearchException(
            'Validation error', 
            array(
                'url'   => 'Url is malformed!'
            )
        );

    }

    public function testSnapSearchExceptionExtendsException(){

        $this->assertInstanceOf('\Exception', $this->snapsearch_exception);

    }

    public function testSnapSearchExceptionReturnsAnArrayOfErrorMessages(){

        $errors = $this->snapsearch_exception->get_errors();
        $this->assertInternalType('array', $errors);
        $this->assertEquals('Url is malformed!', $errors['url']);
        $this->assertEquals('Validation error', $this->snapsearch_exception->getMessage());

    }

    public function testSnapSearchExceptionReturnsAStringOfErrorMessages(){

        $error_string = $this->snapsearch_exception->get_error_string();
        $this->assertEquals('Url is malformed!', $error_string);

    }

}