<?php
namespace Evaluate;

use Evaluate\Model\Evaluate;
use Evaluate\Model\EvaluateTable;
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
                'Evaluate\Model\EvaluateTable' =>  function($sm) {
                    $tableGateway = $sm->get('EvaluateTableGateway');
                    $table = new EvaluateTable($tableGateway);
                    return $table;
                },
                'EvaluateTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Evaluate());
                    return new TableGateway('evaluate', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
