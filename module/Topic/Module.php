<?php
namespace Topic;

use Topic\Model\Topic;
use Topic\Model\TopicTable;
use Topic\Model\Tpcontact;
use Topic\Model\TpcontactTable;
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
                'Topic\Model\TopicTable' =>  function($sm) {
                    $tableGateway = $sm->get('TopicTableGateway');
                    $table = new TopicTable($tableGateway);
                    return $table;
                },
                'TopicTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Topic());
                    return new TableGateway('topic', $dbAdapter, null, $resultSetPrototype);
                },
                'Topic\Model\TpcontactTable' =>  function($sm) {
                    $tableGateway = $sm->get('TpcontactTableGateway');
                    $table = new TpcontactTable($tableGateway);
                    return $table;
                },
                'TpcontactTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Tpcontact());
                    return new TableGateway('tpcontact', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
