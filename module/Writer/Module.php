<?php
namespace Writer;

use Writer\Model\Writer;
use Writer\Model\WriterTable;
use Writer\Model\Wrtmedia;
use Writer\Model\WrtmediaTable;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class Module
{
    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'Writer\Model\WriterTable' =>  function($sm) {
                    $tableGateway = $sm->get('WriterTableGateway');
                    $table = new WriterTable($tableGateway);
                    return $table;
                },
                'WriterTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Writer());
                    return new TableGateway('writer', $dbAdapter, null, $resultSetPrototype);
                },
                'Writer\Model\WrtmediaTable' =>  function($sm) {
                    $tableGateway = $sm->get('WrtmediaTableGateway');
                    $table = new WrtmediaTable($tableGateway);
                    return $table;
                },
                'WrtmediaTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Wrtmedia());
                    return new TableGateway('wrtmedia', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
