<?php
namespace Credit\Model;

use Zend\Db\TableGateway\TableGateway;      
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class CreditTable
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

    public function getCredit($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_credit' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getCreditByFkUser($fk_user)
    {
        $fk_user = (int) $fk_user;
        $rowset = $this->tableGateway->select(array('fk_user' => $fk_user));
        $row = $rowset->current();
        /*if (!$row) {
            throw new \Exception("Could not find row $fk_user");
        }*/
        return $row;
    }

    public function saveCredit(Credit $credit)
    {
        $data = array(
            'fk_user'      => $credit->fk_user,
            'fk_user_type' => $credit->fk_user_type,
            'amount'       => $credit->amount,
            'created_at'   => $credit->created_at,
            'created_by'   => $credit->created_by,
        );

        $id = (int)$credit->id_credit;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCredit($id)) {
                $this->tableGateway->update($data, array('id_credit' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteCredit($id)
    {
        $this->tableGateway->delete(array('id_credit' => $id));
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
            $order = self::ORDER_DEFAULT)
    {
        //新建select对象
        $select = new Select('credit');
        //构建查询条件
        $closure = function (Where $where) use($keyword) {
                    if ($keyword != '') {
                        $where->like('title', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                };
        $select->columns(array('id', 'title', 'artist'))
                ->where($closure);
        if ($order == self::ORDER_LATEST) {
            $select->order('id DESC');//按时间倒排序
        } else {
            $select->order('title ASC');//按标题排序
        }
        //将返回的结果设置为Ablum的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Credit());
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