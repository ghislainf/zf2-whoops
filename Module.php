<?php
namespace Zf2Whoops;

use Zend\EventManager\EventInterface;
use Zend\ModuleManager\Feature\BootstrapListenerInterface;
use Zend\Console\Request as ConsoleRequest;
use Zend\Stdlib\ResponseInterface as Response;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Whoops\Run;
use Whoops\Handler\JsonResponseHandler;
use Whoops\Handler\PrettyPageHandler;

class Module implements BootstrapListenerInterface
{
    /**
     * @var Whoops\Run
     */
    protected $run = null;

    /**
     * @var array
     */
    protected $noCatchExceptions = array();

    /**
     * {@inheritDoc}
     */
    public function onBootstrap(EventInterface $e)
    {
        $config  = $e->getTarget()->getServiceManager()->get('Config');
        $config  = isset($config['view_manager']) ? $config['view_manager'] : array();

        if ($e->getRequest() instanceof ConsoleRequest || empty($config['display_exceptions'])) {
            return;
        }

        $this->run = new Run();
        $this->run->register();

        // set up whoops config
        $prettyPageHandler = new PrettyPageHandler();

        if (isset($config['editor'])) {
            $prettyPageHandler->setEditor($config['editor']);
        }

        if (!empty($config['json_exceptions']['display'])) {
            $jsonHandler = new JsonResponseHandler();

            if (!empty($config['json_exceptions']['show_trace'])) {
                $jsonHandler->addTraceToOutput(true);
            }
            if (!empty($config['json_exceptions']['ajax_only'])) {
                $jsonHandler->onlyForAjaxRequests(true);
            }

            $this->run->pushHandler($jsonHandler);
        }

        if (!empty($config['whoops_no_catch'])) {
            $this->noCatchExceptions = $config['whoops_no_catch'];
        }

        $this->run->pushHandler($prettyPageHandler);

        $eventManager = $e->getTarget()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'prepareException'));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareException'));
    }

    /**
     * Whoops handle exceptions
     * @param Zend\Mvc\MvcEvent $e
     */
    public function prepareException(MvcEvent $e)
    {
        $error = $e->getError();
        if (!empty($error) && !$e->getResult() instanceof Response) {
            switch ($error) {
                case Application::ERROR_CONTROLLER_NOT_FOUND:
                case Application::ERROR_CONTROLLER_INVALID:
                case Application::ERROR_ROUTER_NO_MATCH:
                    // Specifically not handling these
                    return;

                case Application::ERROR_EXCEPTION:
                default:
                    if (in_array(get_class($e->getParam('exception')), $this->noCatchExceptions)) {
                        // No catch this exception
                        return;
                    }

                    $response = $e->getResponse();
                    if (!$response || $response->getStatusCode() === 200) {
                        header('HTTP/1.0 500 Internal Server Error', true, 500);
                    }

                    ob_clean();
                    $this->run->handleException($e->getParam('exception'));
                    break;
            }
        }
    }
}
