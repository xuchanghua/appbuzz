<?php
namespace Credit\Model;

use Zend\Db\TableGateway\TableGateway;      
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;
use DateTime;

class CreditlogTable
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

    public function fetchLogByFkCredit($fk_credit)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($fk_credit){
            $select->where->equalTo('fk_credit', $fk_credit);
            $select->order('id_creditlog DESC');
        });
        return $resultSet;
    }

    public function fetchLogByFkCreditLimit5($fk_credit)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($fk_credit){
            $select->where->equalTo('fk_credit', $fk_credit);
            $select->order('id_creditlog DESC');
            $select->limit(5);
        });
        return $resultSet;
    }

    public function getCreditlog($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_creditlog' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveCreditlog(Creditlog $creditlog)
    {
        $data = array(
            'fk_credit'       => $creditlog->fk_credit,
            'fk_service_type' => $creditlog->fk_service_type,
            'fk_from'         => $creditlog->fk_from,
            'fk_to'           => $creditlog->fk_to,
            'date_time'       => $creditlog->date_time,
            'amount'          => $creditlog->amount,
            'is_pay'          => $creditlog->is_pay,
            'is_charge'       => $creditlog->is_charge,
            'created_at'      => $creditlog->created_at,
            'created_by'      => $creditlog->created_by,
        );

        $id = (int)$creditlog->id_creditlog;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getCreditlog($id)) {
                $this->tableGateway->update($data, array('id_creditlog' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteCreditlog($id)
    {
        $this->tableGateway->delete(array('id_creditlog' => $id));
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