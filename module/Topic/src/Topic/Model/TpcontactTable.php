<?php
namespace Topic\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;
use DateTime;

class TpcontactTable
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

    public function fetchTpcontactByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_tpcontact DESC');
        });
        return $rowset;
    }

    public function fetchTpcontactByFkTopic($fk_topic)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($fk_topic){
            $select->where->equalTo('fk_topic', $fk_topic);
            $select->order('id_tpcontact ASC');
        });
        return $rowset;
    }

    public function getTpcontact($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_tpcontact' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getTpcontactByFkTpAndUser($fk_topic, $created_by)
    {
        $fk_topic = (int) $fk_topic;
        $rowset = $this->tableGateway->select(array(
            'fk_topic' => $fk_topic,
            'created_by' => $created_by,
            ));
        $row = $rowset->current();
        return $row;
    }

    public function saveTpcontact(Tpcontact $tpcontact)
    {
        $data = array(
            'fk_topic'            => $tpcontact->fk_topic,
            'fk_enterprise_user'  => $tpcontact->fk_enterprise_user,
            'fk_media_user'       => $tpcontact->fk_media_user,
            'fk_product'          => $tpcontact->fk_product,
            'created_by'          => $tpcontact->created_by,
            'created_at'          => $tpcontact->created_at,
            'updated_by'          => $tpcontact->updated_by,
            'updated_at'          => $tpcontact->updated_at,
            'order_no'            => $tpcontact->order_no,
            //'matching_degree'     => $tpcontact->matching_degree,
            'fk_tpcontact_status' => $tpcontact->fk_tpcontact_status,
            'attachment'          => $tpcontact->attachment,
        );

        $id = (int)$tpcontact->id_tpcontact;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getTpcontact($id)) {
                $this->tableGateway->update($data, array('id_tpcontact' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function getId($created_at, $created_by)
    {
        $rowset = $this->tableGateway->select(array(
            'created_at' => $created_at,
            'created_by' => $created_by,
        ));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row by $created_at and $created_by");
        }
        return $row->id_tpcontact;
    }

    public function deleteTpcontact($id)
    {
        $this->tableGateway->delete(array('id_tpcontact' => $id));
    }
}