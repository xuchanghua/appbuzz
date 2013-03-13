<?php
namespace Writer\Model;

use Zend\Db\TableGateway\TableGateway;

class WriterTable
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

    public function getWriter($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveWriter(Writer $writer)
    {
        $data = array(
            'artist' => $writer->artist,
            'title'  => $writer->title,
        );

        $id = (int)$writer->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getWriter($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteWriter($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}