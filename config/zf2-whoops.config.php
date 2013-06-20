<?php
return array(
    'whoops' => array(
        // Logger service name
        'logger' => '',

        // These exceptions will be ignored by Whoops and logger
        'ignored_exceptions' => array(
            'BjyAuthorize\Exception\UnAuthorizedException'
        ),
        // editor that Whoops PrettyPageHandler will use
        'editor' => 'sublime',

        // JsonHandler configuration
        'json_exceptions' => array(
            'display' => true,
            'ajax_only' => true,
            'show_trace' => true
        ),
    ),
    'service_manager' => array(
        'factories' => array(
            // Register whoops as a service
            'Whoops' => 'Zf2Whoops\WhoopsFactory',

            // Example logger
            /*'Logger' => function ($sm) {
                $filename = 'log_' . date('F') . '.txt';
                $log = new \Zend\Log\Logger();
                $writer = new \Zend\Log\Writer\Stream('./data/logs/' . $filename);
                $log->addWriter($writer);
                return $log;
            }*/
        ),
    ),
    'view_manager' => array(
        // Used to hide Whoops from production
        //'display_exceptions' => true,
    ),
);
