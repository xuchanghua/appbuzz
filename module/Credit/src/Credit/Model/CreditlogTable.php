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

    public function fetchExportLogByFkCredit($fk_credit)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($fk_credit){
            $select->columns(array('id_creditlog', 'order_no', 'date_time',  
                'amount', 'is_pay', 'is_charge', 'deposit', 'is_pay_deposit', 'is_charge_deposit', 'created_at', 'created_by'));
            $select->join(
                array('st' => 'service_type'),
                'st.id_service_type = creditlog.fk_service_type',
                array(
                    'st_description' => 'description',
                ),
                'left'
            );
            $select->join(
                array('u1' => 'user'),
                'u1.id = creditlog.fk_from',
                array(
                    'u1_username' => 'username',
                ),
                'left'
            );
            $select->join(
                array('u2' => 'user'),
                'u2.id = creditlog.fk_to',
                array(
                    'u2_username' => 'username',
                ),
                'left'
            );
            $select->where->equalTo('fk_credit', $fk_credit);
            $select->order('id_creditlog DESC');
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

    public function getId($created_at, $created_by)
    {
        $rowset = $this->tableGateway->select(array(
            'created_at' => $created_at,
            'created_by' => $created_by,
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row->id_creditlog;
    }

    public function saveCreditlog(Creditlog $creditlog)
    {
        $data = array(
            'fk_credit'         => $creditlog->fk_credit,
            'fk_service_type'   => $creditlog->fk_service_type,
            'fk_from'           => $creditlog->fk_from,
            'fk_to'             => $creditlog->fk_to,
            'date_time'         => $creditlog->date_time,
            'amount'            => $creditlog->amount,
            'is_pay'            => $creditlog->is_pay,
            'is_charge'         => $creditlog->is_charge,
            'order_no'          => $creditlog->order_no,
            'remaining_balance' => $creditlog->remaining_balance,
            'deposit'           => $creditlog->deposit,
            'remaining_deposit' => $creditlog->remaining_deposit,
            'is_pay_deposit'    => $creditlog->is_pay_deposit,
            'is_charge_deposit' => $creditlog->is_charge_deposit,
            'created_at'        => $creditlog->created_at,
            'created_by'        => $creditlog->created_by,
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
        $fk_credit = NULL,
        $keyword = NULL, 
        $page = 1, 
        $itemsPerPage = 10, 
        $order = self::ORDER_DEFAULT)
    {
        //新建select对象
        $select = new Select('creditlog');
        //构建查询条件
        $closure = function (Where $where) use ($keyword, $fk_credit) {
                    if ($keyword != '') {
                        $where->like('title', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                    if (!$fk_credit) {
                        $where->equalTo('fk_credit', $fk_credit);
                    }
                };
        $select->where($closure);
        if ($order == self::ORDER_LATEST) {
            $select->order('id_creditlog DESC');//按id倒排序
        } else {
            $select->order('created_at DESC');//按created_at倒排序
        }
        //将返回的结果设置为Creditlog的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Creditlog());
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