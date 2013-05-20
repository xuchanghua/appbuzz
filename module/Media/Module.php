<?php
namespace Media;

use Media\Model\Media;
use Media\Model\MediaTable;
use Media\Model\Pubmedia;
use Media\Model\PubmediaTable;
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
                'Media\Model\MediaTable' =>  function($sm) {
                    $tableGateway = $sm->get('MediaTableGateway');
                    $table = new MediaTable($tableGateway);
                    return $table;
                },
                'MediaTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Media());
                    return new TableGateway('media', $dbAdapter, null, $resultSetPrototype);
                },
                'Media\Model\PubmediaTable' =>  function($sm) {
                    $tableGateway = $sm->get('PubmediaTableGateway');
                    $table = new PubmediaTable($tableGateway);
                    return $table;
                },
                'PubmediaTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Pubmedia());
                    return new TableGateway('pubmedia', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
