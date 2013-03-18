<?php
namespace Newspub\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class NewspubTable
{
    const ORDER_DEFAULT = 0;
    const ORDER_LATEST  = 1;

    protected $tableGateway;

    public function __construct(TableGateway $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {
        $resultSet = $this->tableGateway->select();
        return $resultSet;
    }

    public function getNewspubByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_newspub DESC');
        });
        //die(var_dump($rowset));
        return $rowset;
    }

    public function getNewspub($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_newspub' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveNewspub(Newspub $newspub)
    {
        $data = array(
            'title'             => $newspub->title,
            'body'              => $newspub->body,
            'download_link'     => $newspub->download_link,
            'appstore_links'    => $newspub->appstore_links,
            'androidmkt_link'   => $newspub->androidmkt_link,
            'barcode'           => $newspub->barcode,
            'fk_pub_mode'       => $newspub->fk_pub_mode,
            'created_by'        => $newspub->created_by,
            'created_at'        => $newspub->created_at,
            'updated_at'        => $newspub->updated_at,
            'updated_by'        => $newspub->updated_by,
            'fk_newspub_status' => $newspub->fk_newspub_status,
            'fk_product'        => $newspub->fk_product,
        );

        $id = (int)$newspub->id_newspub;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getNewspub($id)) {
                $this->tableGateway->update($data, array('id_newspub' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteNewspub($id)
    {
        $this->tableGateway->delete(array('id_newspub' => $id));
    }

    /**
     * 分页获取数据
     * @param string $keyword 图片title的关键词
     * @param int $page 当前页码，从1开始
     * @param int $itemsPerPage 每页结果条数
     * @return \Zend\Paginator\Paginator
     *//*
    public function getPaginator(
            $keyword = NULL, 
            $page = 1, 
            $itemsPerPage = 10, 
            $order = self::ORDER_DEFAULT)
    {
        //新建select对象
        $select = new Select('newspub');
        //构建查询条件
        $closure = function (Where $where) use($keyword) {
                    if ($keyword != '') {
                        $where->like('title', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                };
        //$select->columns(array('id', 'title', 'artist'))->where($closure);
        $select->columns(array('id_newspub', 'title', 'body', 'download_link', 'appstore_links', 'barcode', 'fk_pub_mode','fk_newspub_status'))
                ->where($closure);
        if ($order == self::ORDER_LATEST) {
            $select->order('id_newspub DESC');//按时间倒排序
        } else {
            $select->order('title ASC');//按标题排序
        }
        //将返回的结果设置为Newspub的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Newspub());
        //创建分页用的适配器，第2个参数为数据库adapter，使用全局默认的即可        
        $adapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
        //新建分页
        $paginator = new Paginator($adapter);
        //设置当前页数
        $paginator->setCurrentPageNumber($page);
        //设置一页要返回的结果条数
        $paginator->setItemCountPerPage($itemsPerPage);
        return $paginator;
    }
    */
    /**
     * 分页获取数据
     * @param string $keyword 图片title的关键词
     * @param int $page 当前页码，从1开始
     * @param int $itemsPerPage 每页结果条数
     * @return \Zend\Paginator\Paginator
     */
    public function getPaginator(
            $keyword = NULL, 
            $page = 1, 
            $itemsPerPage = 10, 
            $order = self::ORDER_DEFAULT,
            $created_by)
    {
        //新建select对象
        $select = new Select('newspub');
        //构建查询条件
        $closure = function (Where $where) use($keyword) {
                    if ($keyword != '') {
                        $where->like('title', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                };
        //$select->columns(array('id', 'title', 'artist'))->where($closure);
        $select->columns(array('id_newspub', 'title', 'body', 'download_link', 'appstore_links', 'barcode', 'fk_pub_mode','fk_newspub_status'))
                ->where($closure);
        if($created_by)
        {
            $select->where->equalTo('created_by', $created_by);
        }
        if ($order == self::ORDER_LATEST) {
            $select->order('id_newspub DESC');//按时间倒排序
        } else {
            $select->order('title ASC');//按标题排序
        }
        //将返回的结果设置为Newspub的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Newspub());
        //创建分页用的适配器，第2个参数为数据库adapter，使用全局默认的即可        
        $adapter = new DbSelect($select, $this->tableGateway->getAdapter(), $resultSetPrototype);
        //新建分页
        $paginator = new Paginator($adapter);
        //设置当前页数
        $paginator->setCurrentPageNumber($page);
        //设置一页要返回的结果条数
        $paginator->setItemCountPerPage($itemsPerPage);
        return $paginator;
    }
}