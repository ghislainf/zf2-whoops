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
     * @var Run
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
        $request = $e->getRequest();

        if ($request instanceof ConsoleRequest || empty($config['display_exceptions'])) {
            return;
        }

        $this->run = new Run();

        if( $request instanceof Request && $request->isXmlHttpRequest() )
        {
            $jsonHandler = new JsonResponseHandler();
            $jsonHandler->onlyForAjaxRequests(true);

            if (!empty($config['json_exceptions']['show_trace'])) {
                $jsonHandler->addTraceToOutput(true);
            }

            $this->run->pushHandler($jsonHandler);
        }
        else
        {
            $prettyPageHandler = new PrettyPageHandler();

            if( isset($config['editor']) )
            {
                if( $config['editor'] == 'phpStorm' )
                {
                    $localPath = null;
                    if( isset($config['local_path']) )
                    {
                        $localPath = $config['local_path'];
                    }

                    $prettyPageHandler->setEditor(
                        function ( $file, $line ) use ( $localPath )
                        {
                            if( $localPath )
                            {
                                // if your development server is not local it's good to map remote files to local
                                $translations = array( '^' . __DIR__ => $config['editor_path'] ); // change to your path

                                foreach( $translations as $from => $to )
                                {
                                    $file = preg_replace( '#' . $from . '#', $to, $file, 1 );
                                }
                            }

                            return "pstorm://$file:$line";
                        }
                    );
                }
                else
                {
                    $prettyPageHandler->setEditor( $config['editor'] );
                }
            }
            $this->run->pushHandler($prettyPageHandler);
        }

        if (!empty($config['whoops_no_catch']))
        {
            $this->noCatchExceptions = $config['whoops_no_catch'];
        }

        $this->run->register();

        $eventManager = $e->getTarget()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($this, 'prepareException'));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'prepareException'));
    }

    /**
     * Whoops handle exceptions
     * @param MvcEvent $e
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
