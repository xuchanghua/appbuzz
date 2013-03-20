<?php
namespace Evaluate\Model;

use Zend\Db\TableGateway\TableGateway;   
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class EvaluateTable
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

    public function fetchEvaluateByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_evaluate DESC');
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
            throw new \Exception("Could not find row $id");
        }
        return $row->id_evaluate;
    }

    public function saveEvaluate(Evaluate $evaluate)
    {
        $data = array(
            'fk_product'      => $evaluate->fk_product,
            'highlight'       => $evaluate->highlight,
            'web_link'        => $evaluate->web_link,
            'appstore_link'   => $evaluate->appstore_link,
            'androidmkt_link' => $evaluate->androidmkt_link,
            'barcode'         => $evaluate->barcode,
            'created_by'      => $evaluate->created_by,
            'created_at'      => $evaluate->created_at,
            'updated_by'      => $evaluate->updated_by,
            'updated_at'      => $evaluate->updated_at,
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
}