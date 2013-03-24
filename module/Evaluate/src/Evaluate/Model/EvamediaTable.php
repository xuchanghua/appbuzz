<?php
namespace Evaluate\Model;

use Zend\Db\TableGateway\TableGateway;   
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class EvamediaTable
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

    public function fetchEvamediaByFkEva($fk_evaluate)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($fk_evaluate){
            $select->where->equalTo('fk_evaluate', $fk_evaluate);
            $select->order('id_evamedia DESC');
        });
        return $rowset;
    }

    public function getEvamedia($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_evamedia' => $id));
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
        return $row->id_evamedia;
    }

    public function saveEvamedia(Evamedia $evamedia)
    {
        $data = array(
            'fk_evaluate'        => $evamedia->fk_evaluate,
            'fk_enterprise_user' => $evamedia->fk_enterprise_user,
            'fk_media_user'      => $evamedia->fk_media_user,
            'created_by'         => $evamedia->created_by,
            'created_at'         => $evamedia->created_at,
            'updated_by'         => $evamedia->updated_by,
            'updated_at'         => $evamedia->updated_at,
        );

        $id = (int)$evamedia->id_evamedia;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getEvamedia($id)) {
                $this->tableGateway->update($data, array('id_evamedia' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteEvamedia($id)
    {
        $this->tableGateway->delete(array('id_evamedia' => $id));
    }
}