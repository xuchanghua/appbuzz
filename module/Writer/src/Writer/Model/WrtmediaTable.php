<?php
namespace Writer\Model;

use Zend\Db\TableGateway\TableGateway;   
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class WrtmediaTable
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

    public function fetchWrtmediaByFkWrt($fk_writer)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($fk_writer){
            $select->where->equalTo('fk_writer', $fk_writer);
            $select->order('id_wrtmedia DESC');
        });
        return $rowset;
    }

    public function fetchWrtmediaByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_wrtmedia DESC');
        });
        return $rowset;
    }

    public function fetchWmExRejByMedByFkWrt($fk_writer)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($fk_writer){
            $select->where->equalTo('fk_writer', $fk_writer);
            $select->where->notequalTo('fk_wrtmedia_status',2);
            $select->order('id_wrtmedia DESC');
        });
        return $rowset;
    }
    
    public function getWrtmedia($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_wrtmedia' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getWrtmediaByUserAndFkWrt($created_by, $fk_writer)
    {
        $rowset = $this->tableGateway->select(array(
            'created_by' => $created_by,
            'fk_writer' => $fk_writer,
        ));
        $row = $rowset->current();
        /*if (!$row) {
            throw new \Exception("Could not find row by $created_by and $fk_writer");
        }*/
        return $row;
    }

    public function getId($created_at, $created_by)
    {
        $rowset = $this->tableGateway->select(array(
            'created_at' => $created_at,
            'created_by' => $created_by,
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $created_at, $created_by");
        }
        return $row->id_wrtmedia;
    }

    public function saveWrtmedia(Wrtmedia $wrtmedia)
    {
        $data = array(
            'fk_writer'          => $wrtmedia->fk_writer,
            'fk_enterprise_user' => $wrtmedia->fk_enterprise_user,
            'fk_media_user'      => $wrtmedia->fk_media_user,
            'created_by'         => $wrtmedia->created_by,
            'created_at'         => $wrtmedia->created_at,
            'updated_by'         => $wrtmedia->updated_by,
            'updated_at'         => $wrtmedia->updated_at,
            'fk_wrtmedia_status' => $wrtmedia->fk_wrtmedia_status,
        );

        $id = (int)$wrtmedia->id_wrtmedia;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getWrtmedia($id)) {
                $this->tableGateway->update($data, array('id_wrtmedia' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteWrtmedia($id)
    {
        $this->tableGateway->delete(array('id_wrtmedia' => $id));
    }

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
        $select = new Select('wrtmedia');
        //构建查询条件
        $closure = function (Where $where) use($keyword) {
                    if ($keyword != '') {
                        $where->like('requirement', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                };
        $select->where($closure);
        if($created_by)
        {
            $select->where->equalTo('created_by', $created_by);
        }
        if ($order == self::ORDER_LATEST) {
            $select->order('id_wrtmedia DESC');//按时间倒排序
        } else {
            $select->order('requirement ASC');//按标题排序
        }
        //将返回的结果设置为Wrtmedia的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Wrtmedia());
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