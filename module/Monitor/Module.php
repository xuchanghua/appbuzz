<?php
namespace Monitor;

use Monitor\Model\Monitor;
use Monitor\Model\MonitorTable;
use Monitor\Model\Keyword;
use Monitor\Model\KeywordTable;
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
                'Monitor\Model\MonitorTable' =>  function($sm) {
                    $tableGateway = $sm->get('MonitorTableGateway');
                    $table = new MonitorTable($tableGateway);
                    return $table;
                },
                'MonitorTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Monitor());
                    return new TableGateway('monitor', $dbAdapter, null, $resultSetPrototype);
                },
                'Monitor\Model\KeywordTable' =>  function($sm) {
                    $tableGateway = $sm->get('KeywordTableGateway');
                    $table = new KeywordTable($tableGateway);
                    return $table;
                },
                'KeywordTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Keyword());
                    return new TableGateway('keyword', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
