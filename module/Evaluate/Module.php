<?php
namespace Evaluate;

use Evaluate\Model\Evaluate;
use Evaluate\Model\EvaluateTable;
use Evaluate\Model\Evamedia;
use Evaluate\Model\EvamediaTable;
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
                'Evaluate\Model\EvamediaTable' =>  function($sm) {
                    $tableGateway = $sm->get('EvamediaTableGateway');
                    $table = new EvamediaTable($tableGateway);
                    return $table;
                },
                'EvamediaTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Evamedia());
                    return new TableGateway('evamedia', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
