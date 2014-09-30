<?php namespace Error;

use Closure;
use ReflectionFunction;
use ErrorException;

class FinalException extends \Exception {
    
}

class Handler {

    /**
     * All of the register exception handlers.
     *
     * @var array
     */
    protected $handlers = array();
    
    /**
     * The final fallback error handler
     * 
     * @var closure 
     */
    protected $fallback;
    
    public function __construct()
    {
        $this->registerHandlers();
    }
    
    /**
     * Register the PHP error handler.
     *
     * @return void
     */
    protected function registerErrorHandler()
    {
        set_error_handler(array($this, 'handleError'));
    }

    /**
     * Register the PHP exception handler.
     *
     * @return void
     */
    protected function registerExceptionHandler()
    {
        set_exception_handler(array($this, 'handleException'));
    }

    /**
     * Register the PHP shutdown handler.
     *
     * @return void
     */
    protected function registerShutdownHandler()
    {
        register_shutdown_function(array($this, 'handleShutdown'));
    }

    /**
     * Handle a PHP error for the application.
     *
     * @param  int     $level
     * @param  string  $message
     * @param  string  $file
     * @param  int     $line
     * @param  array   $context
     */
    public function handleError($level, $message, $file = '', $line = 0, $context = array())
    {
        if (error_reporting() & $level)
        {
            throw new ErrorException($message, 0, $level, $file, $line);
        }
    }

    /**
     * Handle an exception for the application.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function handleException($exception, $exit = true)
    {
        $handled = $this->callCustomHandlers($exception);

        // If no response was sent by this custom exception handler, we will call the
        // default exception displayer for the current application context and let
        // it show the exception to the user / developer based on the situation.

        $handled or $this->callFallback($exception);

        if($exit) exit(1); // removed to allow chaining of handlers - check thi
    }

    /**
     * Handle the PHP shutdown event.
     *
     * @return void
     */
    public function handleShutdown(array $errorGetLast = null)
    {
        $error = $errorGetLast ?: error_get_last();

        // If an error has occurred that has not been displayed, we will create a fatal
        // error exception instance and pass it into the regular exception handling
        // code so it can be displayed back out to the developer for information.
        if (isset($error))
        {
            extract($error);

            if (!$this->isFatal($type))
                return;

            $this->handleException(new FatalErrorException($message, $type, 0, $file, $line), false);
        }
    }

    /**
     * Determine if the error type is fatal.
     *
     * @param  int   $type
     * @return bool
     */
    protected function isFatal($type)
    {
        return in_array($type, array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE));
    }

    /**
     * Handle the given exception.
     *
     * @param  Exception  $exception
     * @param  bool  $fromConsole
     * @return void
     */
    protected function callCustomHandlers($exception)
    {
        foreach ($this->handlers as $handler) {
            // If this exception handler does not handle the given exception, we will just
            // go the next one. A handler may type-hint an exception that it handles so
            //  we can have more granularity on the error handling for the developer.
            if (!$this->handlesException($handler, $exception))
            {
                continue;
            } else
            {
                $code = $exception->getCode();
            }

            // We will wrap this handler in a try / catch and avoid white screens of death
            // if any exceptions are thrown from a handler itself. This way we will get
            // at least some errors, and avoid errors with no data or not log writes.
            try {
                $handler($exception, $code);
            } catch (\Exception $e) {
                
                $response = 'Error in exception handler: ' . $this->formatException($e);
                
                $this->callFallback(new FinalException($response, 0, $exception));
            }

            // If this handler returns a "non-false" response (null is a non-false response), we will return it so it will
            // get sent back to the browsers. Once the handler returns a valid response
            // we will cease iterating through them and calling these other handlers.
            return true;
        }
    }

    /**
     * Determine if the given handler handles this exception.
     *
     * @param  Closure    $handler
     * @param  Exception  $exception
     * @return bool
     */
    protected function handlesException(Closure $handler, $exception)
    {
        $reflection = new ReflectionFunction($handler);

        return $reflection->getNumberOfParameters() == 0 || $this->hints($reflection, $exception);
    }

    /**
     * Determine if the given handler type hints the exception.
     *
     * @param  ReflectionFunction  $reflection
     * @param  Exception  $exception
     * @return bool
     */
    protected function hints(ReflectionFunction $reflection, $exception)
    {
        $parameters = $reflection->getParameters();

        $expected = $parameters[0];

        return !$expected->getClass() or $expected->getClass()->isInstance($exception);
    }

    /**
     * Format an exception thrown by a handler.
     *
     * @param  Exception  $e
     * @return string
     */
    protected function formatException(\Exception $e)
    {
        $location = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

        return $location;
    }

    /**
     * Register an application error handler.
     *
     * @param  Closure  $callback
     * @return void
     */
    public function unshiftError(Closure $callback)
    {
        array_unshift($this->handlers, $callback);
        
        return $this;
    }

    /**
     * Register an application error handler at the bottom of the stack.
     *
     * @param  Closure  $callback
     * @return void
     */
    public function error(Closure $callback)
    {
        $this->handlers[] = $callback;
        
        return $this;
    }
    
    /**
     * 
     * @param Closure $callback
     */
    public function fallback(Closure $callback)
    {
        $this->fallback = $callback;
        
        return $this;
    }
    
    /**
     * 
     * @param \Exception $e
     */
    protected function callFallback(\Exception $e)
    {
        if($fallback = $this->fallback)
        {
            $fallback($e);
        }else{
            throw $e;
        }
    }

    /**
     * Register the exception / error handlers for the application.
     *
     * @param  string  $environment
     * @return void
     */
    protected function registerHandlers()
    {
        $this->registerErrorHandler();

        $this->registerExceptionHandler();

        $this->registerShutdownHandler();
    }

}
