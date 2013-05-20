<?php
namespace Credit;

use Credit\Model\Credit;
use Credit\Model\CreditTable;
use Credit\Model\Creditlog;
use Credit\Model\CreditlogTable;
use Credit\Model\Constant;
use Credit\Model\ConstantTable;
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
                'Credit\Model\CreditTable' =>  function($sm) {
                    $tableGateway = $sm->get('CreditTableGateway');
                    $table = new CreditTable($tableGateway);
                    return $table;
                },
                'CreditTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Credit());
                    return new TableGateway('credit', $dbAdapter, null, $resultSetPrototype);
                },
                'Credit\Model\CreditlogTable' =>  function($sm) {
                    $tableGateway = $sm->get('CreditlogTableGateway');
                    $table = new CreditlogTable($tableGateway);
                    return $table;
                },
                'CreditlogTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Creditlog());
                    return new TableGateway('creditlog', $dbAdapter, null, $resultSetPrototype);
                },
                'Credit\Model\ConstantTable' =>  function($sm) {
                    $tableGateway = $sm->get('ConstantTableGateway');
                    $table = new ConstantTable($tableGateway);
                    return $table;
                },
                'ConstantTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Constant());
                    return new TableGateway('constant', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
