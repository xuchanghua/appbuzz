<?php
namespace Enterprise;

use Enterprise\Model\Enterprise;
use Enterprise\Model\EnterpriseTable;
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
                'Enterprise\Model\EnterpriseTable' =>  function($sm) {
                    $tableGateway = $sm->get('EnterpriseTableGateway');
                    $table = new EnterpriseTable($tableGateway);
                    return $table;
                },
                'EnterpriseTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Enterprise());
                    return new TableGateway('enterprise', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
