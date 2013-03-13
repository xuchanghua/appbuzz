<?php
namespace Evaluate\Model;

use Zend\Db\TableGateway\TableGateway;

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

    public function getEvaluate($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveEvaluate(Evaluate $evaluate)
    {
        $data = array(
            'artist' => $evaluate->artist,
            'title'  => $evaluate->title,
        );

        $id = (int)$evaluate->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getEvaluate($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteEvaluate($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}