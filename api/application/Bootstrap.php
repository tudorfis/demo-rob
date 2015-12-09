<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{

    protected function _initMyRoutes() {
        $config = new Zend_Config_Ini(APPLICATION_PATH . '/configs/routes.ini', 'routes');
        $router = Zend_Controller_Front::getInstance()->getRouter();
        $router->addConfig( $config, 'routes' );
    }

    protected function _initRegistryConfig()
    {
        $config = $this->getOptions();
        Zend_Registry::set('__CONFIG__', $config);

        $this->bootstrap('db');
        Zend_Registry::set('__DB__', $this->getResource('db'));

        $this->bootstrap('session');
    }


    protected function _initAutoloader()
    {
        $loader = function($className) {
            $className = str_replace('\\', '_', $className);
            Zend_Loader_Autoloader::autoload($className);
        };

        $autoloader = Zend_Loader_Autoloader::getInstance();
        $autoloader->pushAutoloader($loader, 'App\\');
    }

    protected function _initResourceLoader()
    {
        $resourceLoader = new Zend_Loader_Autoloader_Resource(array(
                'basePath'         => APPLICATION_PATH,
                'namespace'     => '',
                'resourceTypes' => array(
                    'model' => array(
                        'path' => 'models/',
                        'namespace' => 'Model'
                    ),
                    'table' => array(
                        'path' => 'models/Table',
                        'namespace' => 'Table'
                    )
                )
            ));

        return $resourceLoader;
    }

    protected function _initFunctions() {
        include APPLICATION_PATH . '/configs/functions.php';
    }

}

