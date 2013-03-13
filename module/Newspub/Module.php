<?php
namespace Newspub;

use Newspub\Model\Newspub;
use Newspub\Model\NewspubTable;
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
                'Newspub\Model\NewspubTable' =>  function($sm) {
                    $tableGateway = $sm->get('NewspubTableGateway');
                    $table = new NewspubTable($tableGateway);
                    return $table;
                },
                'NewspubTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Newspub());
                    return new TableGateway('newspub', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
