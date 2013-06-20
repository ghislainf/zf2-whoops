<?php
/**
 * Created by Inditel Meedia OÜ
 * User: Oliver
 * Date: 20.06.13 11:39
 */

namespace Zf2Whoops;


use Whoops\Run;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class WhoopsFactory implements FactoryInterface
{

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @return WhoopsInit
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {

        $config = $serviceLocator->get('Config');
        $whoopsConfig = $config['whoops'];

        $viewManagerConfig = $config['view_manager'];

        $run = new Run();
        $logger = null;
        if (isset($whoopsConfig['logger'])) {
            $logger = $serviceLocator->get($whoopsConfig['logger']);
        }

        $exceptionHandler = new ExceptionHandler();
        $exceptionHandler->setLogger($logger);

        $whoops = new WhoopsInit($run, $exceptionHandler);
        $whoops->initFromConfig($whoopsConfig, $viewManagerConfig);

        return $whoops;
    }

}