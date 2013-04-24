<?php
namespace Topic\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;
use DateTime;

class TopicTable
{
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

    public function fetchTopicByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_topic DESC');
        });
        return $rowset;
    }

    public function fetchAllJoinLeftTpcontactDesc()
    {
         $resultSet = $this->tableGateway->select(function(Select $select){
            $select->join(
                array('tc' => 'tpcontact'), //table name
                'tc.fk_topic = topic.id_topic', //on
                array(
                    'tc_order_no'            => 'order_no', 
                    'tc_fk_enterprise_user'  => 'fk_enterprise_user',
                    'tc_fk_media_user'       => 'fk_media_user',
                    'tc_created_at'          => 'created_at',
                    'tc_fk_tpcontact_status' => 'fk_tpcontact_status',
                    'tc_fk_product'          => 'fk_product',
                ), //columns
                'left'
                );
            $select->order('id_topic DESC');
        });
        return $resultSet;
    }

    public function getTopic($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_topic' => $id));
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
        return $row->id_topic;
    }

    public function fetchPastTopic($created_by = null)
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
            $select->order('id_topic DESC');
        });
        return $rowset;
    }

    public function fetchCurrentTopic($created_by = null)
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
            $select->order('id_topic DESC');
        });
        return $rowset;
    }

    public function saveTopic(Topic $topic)
    {
        $data = array(
            'topic_type'      => $topic->topic_type,
            'abstract'        => $topic->abstract,
            'app_type'        => $topic->app_type,
            'created_by'      => $topic->created_by,
            'created_at'      => $topic->created_at,
            'updated_by'      => $topic->updated_by,
            'updated_at'      => $topic->updated_at,
            'due_date'        => $topic->due_date,
            'fk_topic_status' => $topic->fk_topic_status,
            'order_no'        => $topic->order_no,
        );

        $id = (int)$topic->id_topic;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getTopic($id)) {
                $this->tableGateway->update($data, array('id_topic' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteTopic($id)
    {
        $this->tableGateway->delete(array('id_topic' => $id));
    }
}