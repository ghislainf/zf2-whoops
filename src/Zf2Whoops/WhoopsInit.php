<?php
/**
 * Created by Inditel Meedia OÜ
 * User: Oliver
 * Date: 20.06.13 11:35
 */

namespace Zf2Whoops;


use Whoops\Handler\Handler;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;
use Zend\Log\Logger;

class WhoopsInit
{

    /**
     * @var Run
     */
    private $run;
    /**
     * @var boolean
     */
    private $displayExceptions = false;
    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;
    /**
     * @var string
     */
    private $editor;
    /**
     * @var array
     */
    private $jsonHandlerConfig;

    /**
     * @param Run $run
     * @param ExceptionHandler $exceptionHandler
     */
    public function __construct(Run $run, ExceptionHandler $exceptionHandler)
    {
        $this->run = $run;
        $this->exceptionHandler = $exceptionHandler;
        $this->exceptionHandler->setRun($run);
    }

    /**
     * @param array $config
     * @param array $viewManagerConfig
     */
    public function initFromConfig(array $config, array $viewManagerConfig)
    {

        if (isset($viewManagerConfig['display_exceptions'])) {
            $this->displayExceptions = (bool)$viewManagerConfig['display_exceptions'];
        }

        if (isset($config['ignored_exceptions'])) {
            $this->ignoredExceptions = (array)$config['ignored_exceptions'];
        }

        if (isset($config['editor'])) {
            $this->editor = $config['editor'];
        }

        if (isset($config['json_exceptions'])) {
            $this->jsonHandlerConfig = $config['json_exceptions'];
        }

    }

    /**
     *
     */
    public function register()
    {
        $this->run->register();

        if ($this->displayExceptions) {
            $this->setupJsonResponseHandler();
            $this->setupPrettyPageHandler();
        }

        if ($this->exceptionHandler->getLogger()) {
            $this->setupLogger();
        }
    }

    /**
     *
     */
    protected function setupJsonResponseHandler()
    {

        if (!isset($this->jsonHandlerConfig['display'])) {
            return;
        }

        $handler = new JsonResponseHandler();

        if (isset($this->jsonHandlerConfig['show_trace'])) {
            $handler->addTraceToOutput(true);
        }
        if (isset($this->jsonHandlerConfig['ajax_only'])) {
            $handler->onlyForAjaxRequests(true);
        }

        $this->run->pushHandler($handler);
    }

    /**
     *
     */
    protected function setupPrettyPageHandler()
    {

        $handler = new PrettyPageHandler();

        if ($this->editor) {
            $handler->setEditor($this->editor);
        }

        $this->run->pushHandler($handler);
    }

    /**
     *
     */
    protected function setupLogger()
    {
        $whoops = $this;
        $closure = function ($exception, $inspector, $run) use ($whoops) {
            $whoops->getExceptionHandler()->executeLoggerClosure($exception);
            return Handler::DONE;
        };
        $this->run->pushHandler($closure);
    }

    /**
     * @return ExceptionHandler
     */
    public function getExceptionHandler()
    {
        return $this->exceptionHandler;
    }

    /**
     * @return \Whoops\Run
     */
    public function getRun()
    {
        return $this->run;
    }

}