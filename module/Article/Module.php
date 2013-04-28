<?php
namespace Article;

use Article\Model\Article;
use Article\Model\ArticleTable;
use Article\Model\Rank;
use Article\Model\RankTable;
use Article\Model\Banner;
use Article\Model\BannerTable;
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
                'Article\Model\ArticleTable' =>  function($sm) {
                    $tableGateway = $sm->get('ArticleTableGateway');
                    $table = new ArticleTable($tableGateway);
                    return $table;
                },
                'ArticleTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Article());
                    return new TableGateway('article', $dbAdapter, null, $resultSetPrototype);
                },
                'Article\Model\RankTable' =>  function($sm) {
                    $tableGateway = $sm->get('RankTableGateway');
                    $table = new RankTable($tableGateway);
                    return $table;
                },
                'RankTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Rank());
                    return new TableGateway('rank', $dbAdapter, null, $resultSetPrototype);
                },
                'Article\Model\BannerTable' =>  function($sm) {
                    $tableGateway = $sm->get('BannerTableGateway');
                    $table = new BannerTable($tableGateway);
                    return $table;
                },
                'BannerTableGateway' => function ($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $resultSetPrototype = new ResultSet();
                    $resultSetPrototype->setArrayObjectPrototype(new Banner());
                    return new TableGateway('banner', $dbAdapter, null, $resultSetPrototype);
                },
            ),
        );
    }
}
