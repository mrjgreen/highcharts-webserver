<?php
use Error\Handler;

class TestExceptionTypeOne extends Exception
{

}

class TestExceptionTypeTwo extends Exception
{

}

class BasicTest extends PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->handler = new Handler();
    }

    public function testItThrowsUnhandledExceptions()
    {
        $e = new TestExceptionTypeOne("Error Message", 50);
        try {
            $this->handler->handleException($e);
        } catch (TestExceptionTypeOne $error) {}

        $this->assertSame($e, $error);
    }

    public function testItHandlesTypedExceptions()
    {
        $e = new TestExceptionTypeOne("Error Message", 50);

        $error = null;

        $called = false;

        $this->handler
            ->error(function(TestExceptionTypeTwo $e) use(&$called){
                $called = true;
            })
            ->error(function(TestExceptionTypeOne $e) use(&$error){
                $error = $e;
            });

        $this->handler->handleException($e, false);

        $this->assertInstanceOf('TestExceptionTypeOne', $error);
        $this->assertFalse($called);
    }

    public function testItPushesHandlerToTopOfStack()
    {
        $e = new TestExceptionTypeOne("Error Message", 50);

        $error = null;

        $called = false;

        $this->handler
            ->error(function(TestExceptionTypeOne $e) use(&$called){
                $called = true;
            })
            ->unshiftError(function(TestExceptionTypeOne $e) use(&$error){
                $error = $e;
            });

        $this->handler->handleException($e, false);

        $this->assertInstanceOf('TestExceptionTypeOne', $error);
        $this->assertFalse($called);
    }

    public function testItDoesntHandleNonFatalsOnShutdown()
    {
        // Here we assign a dummy "blackhole" error handler and invoke an error
        set_error_handler('var_dump', 0);
        @trigger_error("Undefined Variable", E_NOTICE);

        // Out shutdown function should not attempt to handle it, because it's only a notice level
        // If it does handle it, no callbacks are attached so the fallback will throw the exception and fail the test
        $this->handler->handleShutdown();
    }

    public function testItHandlesFatalsOnShutdown()
    {
        $errorGetLast = array(
            'type'      => E_CORE_ERROR,
            'message'   => "Method does not exist",
            'file'      => "/Users/joegreen/Projects/error/tests/unit/BasicTest.php",
            'line'      => 50
        );

        // Out shutdown function should attempt to handle it, because it's a fatal level
        $error = null;
        try{
            $this->handler->handleShutdown($errorGetLast);
        }catch(\ErrorException $error){}

        $this->assertInstanceOf('ErrorException', $error);
    }

    public function testItThrowsFinalExceptionIfErrorInHandler()
    {
        $this->handler->error(function(TestExceptionTypeOne $e){
            trigger_error('Whoops');
        });

        $error = null;
        try{
            $this->handler->handleException(new TestExceptionTypeOne("Error Message", 50), false);
        }catch(\Error\FinalException $error){}

        $this->assertInstanceOf('Error\FinalException', $error);
    }

    public function testFallBackCanBeAssigned()
    {
        $error = null;

        $this->handler->fallback(function(\Exception $e) use(&$error){
            $error = $e;
        });

        $this->handler->handleException(new TestExceptionTypeOne("Error Message", 50), false);

        $this->assertInstanceOf('TestExceptionTypeOne', $error);
    }

    public function testItHandleErrorExceptionArguments()
    {
        $error = null;
        try {
            $this->handler->handleError(E_USER_ERROR, 'message', '/path/to/file', 111, array());
        } catch (ErrorException $error) {}

        $this->assertInstanceOf('ErrorException', $error);
        $this->assertSame(E_USER_ERROR, $error->getSeverity(), 'error handler should not modify severity');
        $this->assertSame('message', $error->getMessage(), 'error handler should not modify message');
        $this->assertSame('/path/to/file', $error->getFile(), 'error handler should not modify path');
        $this->assertSame(111, $error->getLine(), 'error handler should not modify line number');
        $this->assertSame(0, $error->getCode(), 'error handler should use 0 exception code');
    }


    public function testItHandleErrorOptionalArguments()
    {
        $error = null;
        try {
            $this->handler->handleError(E_USER_ERROR, 'message');
        } catch (ErrorException $error) {}

        $this->assertInstanceOf('ErrorException', $error);
        $this->assertSame('', $error->getFile(), 'error handler should use correct default path');
        $this->assertSame(0, $error->getLine(), 'error handler should use correct default line');
    }
}