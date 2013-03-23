<?php
namespace Topic\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

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

    public function saveTopic(Topic $topic)
    {
        $data = array(
            'topic_type' => $topic->topic_type,
            'abstract'   => $topic->abstract,
            'app_type'   => $topic->app_type,
            'created_by' => $topic->created_by,
            'created_at' => $topic->created_at,
            'updated_by' => $topic->updated_by,
            'updated_at' => $topic->updated_at,
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