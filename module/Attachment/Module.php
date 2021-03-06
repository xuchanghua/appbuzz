<?php
namespace Attachment;

use Attachment\Model\Attachment;
use Attachment\Model\AttachmentTable;
use Attachment\Model\Barcode;
use Attachment\Model\BarcodeTable;
use Attachment\Model\Appicon;
use Attachment\Model\AppiconTable;
use Attachment\Model\Screenshot;
use Attachment\Model\ScreenshotTable;
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
                'Attachment\Model\ScreenshotTable' =>  function($sm) {
                    $tableGateway = $sm->get('ScreenshotTableGateway');
                    $table = new ScreenshotTable($tableGateway);
                    return $table;
                },
                'ScreenshotTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Screenshot());
                    return new TableGateway('screenshot', $dbAdapter, null, $resultSetPrototype);
                },
                'Attachment\Model\AppiconTable' =>  function($sm) {
                    $tableGateway = $sm->get('AppiconTableGateway');
                    $table = new AppiconTable($tableGateway);
                    return $table;
                },
                'AppiconTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Appicon());
                    return new TableGateway('appicon', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
