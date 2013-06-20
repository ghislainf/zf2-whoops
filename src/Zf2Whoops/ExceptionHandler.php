<?php
/**
 * Created by Inditel Meedia OÜ
 * User: Oliver
 * Date: 20.06.13 12:30
 */

namespace Zf2Whoops;


use Closure;
use Whoops\Run;
use Zend\Log\Logger;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface as Response;

class ExceptionHandler
{

    /**
     * @var Logger
     */
    private $logger;
    /**
     * @var Run
     */
    private $run;
    /**
     * @var Closure
     */
    private $loggerExceptionHandler;
    /**
     * @var array
     */
    private $ignoredExceptions = array();

    /**
     * @return \Zend\Log\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    /**
     * @param Logger $logger
     */
    public function setLogger(Logger $logger)
    {
        $this->logger = $logger;
        if ($logger) {
            $this->setLoggerExceptionHandler($logger);
        }
    }

    /**
     * @param Logger $logger
     */
    protected function setLoggerExceptionHandler(Logger $logger)
    {
        if ($this->loggerExceptionHandler) {
            return;
        }
        $dummyClosure = function () {

        };
        // Kind of a hack... Not proud about it...
        // We want to use Logger native closure, unfortunately we can't access it any other way...
        $previousHandler = set_exception_handler($dummyClosure);
        Logger::registerExceptionHandler($this->getLogger());
        $this->loggerExceptionHandler = set_exception_handler($dummyClosure);
        Logger::unregisterExceptionHandler();
        set_exception_handler($previousHandler);
    }

    /**
     * @param MvcEvent $e
     */
    public function exceptionHandler(MvcEvent $e)
    {

        if (!$this->canHandlerError($e)) {
            return;
        }

        switch ($e->getError()) {
            case Application::ERROR_CONTROLLER_NOT_FOUND:
            case Application::ERROR_CONTROLLER_INVALID:
                $this->handleControllerError($e);
                return;

            case Application::ERROR_ROUTER_NO_MATCH:
                $this->handleRouteNoMatchError($e);
                return;

            case Application::ERROR_EXCEPTION:
            default:
                $this->handleException($e);
                return;
        }

    }

    /**
     * @param MvcEvent $e
     * @return bool
     */
    protected function canHandlerError(MvcEvent $e)
    {
        return $e->getError() && !$e->getResult() instanceof Response;
    }

    /**
     * @param MvcEvent $e
     */
    protected function handleControllerError(MvcEvent $e)
    {
        if (!$this->loggerExceptionHandler) {
            return;
        }
        $res = $e->getResult()->getVariables();
        $message = $res->controller . ' (' . $res->controller_class . ')';
        $this->executeLoggerClosure(new \Zend\Mvc\Exception\InvalidControllerException($message));
    }

    /**
     * @param \Exception $exception
     */
    public function executeLoggerClosure(\Exception $exception)
    {
        $loggerExceptionHandler = $this->loggerExceptionHandler;
        $loggerExceptionHandler($exception);
    }

    /**
     * @param MvcEvent $e
     */
    protected function handleRouteNoMatchError(MvcEvent $e)
    {
        if (!$this->loggerExceptionHandler) {
            return;
        }
        $requestUri = $e->getRequest()->getRequestUri();
        $res = $e->getResult()->getVariables();
        $message = $requestUri . ' (' . $res->reason . ', ' . $res->message . ')';
        $this->executeLoggerClosure(new \Zend\Mvc\Router\Exception\RuntimeException($message));
    }

    /**
     * @param MvcEvent $e
     */
    protected function handleException(MvcEvent $e)
    {

        if ($this->shouldIgnoreException($e->getParam('exception'))) {
            $this->executeLoggerClosure($e->getParam('exception'));
            return;
        }

        $response = $e->getResponse();
        if (!$response || $response->getStatusCode() === 200) {
            header('HTTP/1.0 500 Internal Server Error', true, 500);
        }
        ob_clean();
        $this->getRun()->handleException($e->getParam('exception'));
    }

    /**
     * @param \Exception $e
     * @return bool
     */
    public function shouldIgnoreException(\Exception $e)
    {
        return in_array(get_class($e), $this->ignoredExceptions);
    }

    /**
     * @return \Whoops\Run
     */
    public function getRun()
    {
        return $this->run;
    }

    /**
     * @param Run $run
     */
    public function setRun($run)
    {
        $this->run = $run;
    }
}