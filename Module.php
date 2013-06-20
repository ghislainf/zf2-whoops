<?php
namespace Zf2Whoops;


use Whoops\Run;
use Zend\Console\Request as ConsoleRequest;
use Zend\EventManager\EventManagerInterface;
use Zend\Log\Logger;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;

class Module
{

    /**
     * @param MvcEvent $e
     * @return array|void
     */
    public function onBootstrap(MvcEvent $e)
    {

        if (PHP_SAPI === 'cli') {
            return;
        }

        if ($e->getRequest() instanceof ConsoleRequest) {
            return;
        }

        /** @var ServiceManager $serviceManager */
        $serviceManager = $e->getTarget()->getServiceManager();
        /** @var WhoopsInit $handler */
        $handler = $serviceManager->get('Whoops');
        $handler->register();

        /** @var EventManagerInterface $eventManager */
        $eventManager = $e->getTarget()->getEventManager();
        $eventManager->attach(MvcEvent::EVENT_RENDER_ERROR, array($handler->getExceptionHandler(), 'exceptionHandler'));
        $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($handler->getExceptionHandler(), 'exceptionHandler'));
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

}
