<?php
namespace Interview\Model;

use Zend\Db\TableGateway\TableGateway;

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

    public function getInterview($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveInterview(Interview $interview)
    {
        $data = array(
            'artist' => $interview->artist,
            'title'  => $interview->title,
        );

        $id = (int)$interview->id;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getInterview($id)) {
                $this->tableGateway->update($data, array('id' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteInterview($id)
    {
        $this->tableGateway->delete(array('id' => $id));
    }
}