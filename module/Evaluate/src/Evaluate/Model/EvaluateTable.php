<?php
namespace Evaluate\Model;

use Zend\Db\TableGateway\TableGateway;   
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;
use DateTime;

class EvaluateTable
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

    public function fetchAllDesc()
    {
        $resultSet = $this->tableGateway->select(function(Select $select){
            $select->order('id_evaluate DESC');
        });
        return $resultSet;
    }

    public function fetchAllJoinLeftEvamediaDesc()
    {
         $resultSet = $this->tableGateway->select(function(Select $select){
            $select->join(
                array('em' => 'evamedia'), //table name
                'em.fk_evaluate = evaluate.id_evaluate', //on
                array(
                    'em_order_no' => 'order_no', 
                    'em_fk_enterprise_user' => 'fk_enterprise_user',
                    'em_fk_media_user' => 'fk_media_user',
                    'em_created_at' => 'created_at',
                    'em_fk_evamedia_status' => 'fk_evamedia_status',
                ), //columns
                'left'
                );
            $select->order('id_evaluate DESC');
        });
        return $resultSet;
    }

    public function fetchCurrentEvaluate($created_by = null)
    {
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $strdatetime = $datetime->format(DATE_ATOM);
        $now = substr($strdatetime,0,10);
        $rowset = $this->tableGateway->select(function(Select $select) use ($now, $created_by){
            $select->where->greaterThanOrEqualTo('due_date', $now);
            if($created_by)
            {
                $select->where->equalTo('created_by', $created_by);
            }
            $select->order('id_evaluate DESC');
        });
        return $rowset;
    }

    public function fetchPastEvaluate($created_by = null)
    {
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $strdatetime = $datetime->format(DATE_ATOM);
        $now = substr($strdatetime,0,10);
        $rowset = $this->tableGateway->select(function(Select $select) use ($now, $created_by){
            $select->where->lessThan('due_date', $now);
            if($created_by)
            {
                $select->where->equalTo('created_by', $created_by);
            }
            $select->order('id_evaluate DESC');
        });
        return $rowset;
    }

    public function fetchEvaluateByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_evaluate DESC');
        });
        return $rowset;
    }

    public function fetchEvaluateByUserLimit5($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_evaluate DESC');
            $select->limit(5);
        });
        return $rowset;
    }

    public function getEvaluate($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_evaluate' => $id));
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
            throw new \Exception("Could not find row $created_at and $created_by");
        }
        return $row->id_evaluate;
    }

    public function saveEvaluate(Evaluate $evaluate)
    {
        $data = array(
            'fk_product'         => $evaluate->fk_product,
            'highlight'          => $evaluate->highlight,
            'web_link'           => $evaluate->web_link,
            'appstore_link'      => $evaluate->appstore_link,
            'androidmkt_link'    => $evaluate->androidmkt_link,
            'barcode'            => $evaluate->barcode,
            'created_by'         => $evaluate->created_by,
            'created_at'         => $evaluate->created_at,
            'updated_by'         => $evaluate->updated_by,
            'updated_at'         => $evaluate->updated_at,
            'requirement'        => $evaluate->requirement,
            'due_date'           => $evaluate->due_date,
            'order_no'           => $evaluate->order_no,
            'order_limit'        => $evaluate->order_limit,
            'fk_evaluate_status' => $evaluate->fk_evaluate_status,
        );

        $id = (int)$evaluate->id_evaluate;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getEvaluate($id)) {
                $this->tableGateway->update($data, array('id_evaluate' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteEvaluate($id)
    {
        $this->tableGateway->delete(array('id_evaluate' => $id));
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
        $select = new Select('evaluate');
        //构建查询条件
        $closure = function (Where $where) use($keyword) {
                    if ($keyword != '') {
                        $where->like('highlight', '%' . $keyword . '%');//查询符合特定关键词的结果
                    }
                };
        $select->where($closure);
        if($created_by)
        {
            $select->where->equalTo('created_by', $created_by);
        }
        if ($order == self::ORDER_LATEST) {
            $select->order('id_evaluate DESC');//按时间倒排序
        } else {
            $select->order('highlight ASC');//按标题排序
        }
        //将返回的结果设置为Evaluate的实例
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Evaluate());
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