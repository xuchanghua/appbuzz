<?php
namespace Writer\Model;

use Zend\Db\TableGateway\TableGateway;   
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class WriterTable
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

    public function fetchWriterByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_writer DESC');
        });
        return $rowset;
    }

    public function getWriter($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_writer' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
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
        return $row->id_writer;
    }

    public function saveWriter(Writer $writer)
    {
        $data = array(
            'fk_product'      => $writer->fk_product,
            'requirement'     => $writer->requirement,
            'hint'            => $writer->hint,
            'web_link'        => $writer->web_link,
            'appstore_link'   => $writer->appstore_link,
            'androidmkt_link' => $writer->androidmkt_link,
            'barcode'         => $writer->barcode,
            'created_by'      => $writer->created_by,
            'created_at'      => $writer->created_at,
            'updated_by'      => $writer->updated_by,
            'updated_at'      => $writer->updated_at,
        );

        $id = (int)$writer->id_writer;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getWriter($id)) {
                $this->tableGateway->update($data, array('id_writer' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteWriter($id)
    {
        $this->tableGateway->delete(array('id_writer' => $id));
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
        $select = new Select('writer');
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
            $select->order('id_writer DESC');//按时间倒排序
        } else {
            $select->order('requirement ASC');//按标题排序
        }
        //将返回的结果设置为Writer的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Writer());
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