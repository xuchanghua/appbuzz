<?php
namespace Attachment;

use Attachment\Model\Attachment;
use Attachment\Model\AttachmentTable;
use Attachment\Model\Barcode;
use Attachment\Model\BarcodeTable;
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
                'Attachment\Model\AttachmentTable' =>  function($sm) {
                    $tableGateway = $sm->get('AttachmentTableGateway');
                    $table = new AttachmentTable($tableGateway);
                    return $table;
                },
                'AttachmentTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Attachment());
                    return new TableGateway('attachment', $dbAdapter, null, $resultSetPrototype);
                },
                'Attachment\Model\BarcodeTable' =>  function($sm) {
                    $tableGateway = $sm->get('BarcodeTableGateway');
                    $table = new BarcodeTable($tableGateway);
                    return $table;
                },
                'BarcodeTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Barcode());
                    return new TableGateway('barcode', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
