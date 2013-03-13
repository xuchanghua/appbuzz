<?php
namespace Interview;

use Interview\Model\Interview;
use Interview\Model\InterviewTable;
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
                'Interview\Model\InterviewTable' =>  function($sm) {
                    $tableGateway = $sm->get('InterviewTableGateway');
                    $table = new InterviewTable($tableGateway);
                    return $table;
                },
                'InterviewTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Interview());
                    return new TableGateway('interview', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
