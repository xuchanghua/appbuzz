<?php
namespace Interview\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;
use DateTime;

class InterviewTable
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

    public function fetchAllDesc()
    {
        $resultSet = $this->tableGateway->select(function(Select $select){
            $select->order('id_interview DESC');
        });
        return $resultSet;
    }

    public function fetchInterviewByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_interview DESC');
        });
        return $rowset;
    }

    public function fetchCurrentInterview($created_by = null)
    {
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $now = $datetime->format('Y-m-d H:i:s');
        $rowset = $this->tableGateway->select(function(Select $select) use ($now, $created_by){
            $select->where->greaterThanOrEqualTo('date_time', $now);
            if($created_by)
            {
                $select->where->equalTo('created_by', $created_by);
            }
            $select->order('id_interview DESC');
        });
        return $rowset;
    }

    public function fetchPastInterview($created_by = null)
    {
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $now = $datetime->format('Y-m-d H:i:s');
        $rowset = $this->tableGateway->select(function(Select $select) use ($now, $created_by){
            $select->where->lessThan('date_time', $now);
            if($created_by)
            {
                $select->where->equalTo('created_by', $created_by);
            }
            $select->order('id_interview DESC');
        });
        return $rowset;
    }

    public function fetchCurrentEntInterview($fk_enterprise_user = null)
    {
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $now = $datetime->format('Y-m-d H:i:s');
        $rowset = $this->tableGateway->select(function(Select $select) use ($now, $fk_enterprise_user){
            $select->where->greaterThanOrEqualTo('date_time', $now);
            if($fk_enterprise_user)
            {
                $select->where->equalTo('fk_enterprise_user', $fk_enterprise_user);
            }
            $select->order('id_interview DESC');
        });
        return $rowset;
    }

    public function fetchPastEntInterview($fk_enterprise_user = null)
    {
        date_default_timezone_set("Asia/Shanghai");
        $datetime = new DateTime;
        $now = $datetime->format('Y-m-d H:i:s');
        $rowset = $this->tableGateway->select(function(Select $select) use ($now, $fk_enterprise_user){
            $select->where->lessThan('date_time', $now);
            if($fk_enterprise_user)
            {
                $select->where->equalTo('fk_enterprise_user', $fk_enterprise_user);
            }
            $select->order('id_interview DESC');
        });
        return $rowset;
    }

    public function getInterview($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_interview' => $id));
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
        if(!$row){
            throw new \Exception("Could not find row via $created_at and $created_by");
        }
        return $row->id_interview;
    }

    public function saveInterview(Interview $interview)
    {
        $data = array(
            'fk_product'          => $interview->fk_product,
            'fk_enterprise_user'  => $interview->fk_enterprise_user,
            'fk_media_user'       => $interview->fk_media_user,
            'date_time'           => $interview->date_time,
            'purpose'             => $interview->purpose,
            'outline'             => $interview->outline,
            'fk_interview_status' => $interview->fk_interview_status,
            'created_at'          => $interview->created_at,
            'created_by'          => $interview->created_by,
            'updated_at'          => $interview->updated_at,
            'updated_by'          => $interview->updated_by,
            'order_no'            => $interview->order_no,
        );

        $id = (int)$interview->id_interview;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getInterview($id)) {
                $this->tableGateway->update($data, array('id_interview' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteInterview($id)
    {
        $this->tableGateway->delete(array('id_interview' => $id));
    }
}