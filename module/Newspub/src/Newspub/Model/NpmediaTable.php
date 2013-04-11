<?php
namespace Newspub\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

class NpmediaTable
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
            $select->order('id_npmedia DESC');
        });
        return $resultSet;
    }

    public function fetchNpmediaByFkNewspub($fk_newspub)
    {
        $resultSet = $this->tableGateway->select(function(Select $select) use ($fk_newspub){
            $select->where->equalTo('fk_newspub', $fk_newspub);
        });
        return $resultSet;
    }

    public function getNpmediaByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_npmedia DESC');
        });
        //die(var_dump($rowset));
        return $rowset;
    }

    public function getNpmedia($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_npmedia' => $id));
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
        return $row->id_npmedia;
    }

    public function saveNpmedia(Npmedia $npmedia)
    {
        $data = array(
            'fk_newspub'        => $npmedia->fk_newspub,
            'fk_media_user'     => $npmedia->fk_media_user,
            'fk_npmedia_status' => $npmedia->fk_npmedia_status,
            'news_link'         => $npmedia->news_link,
            'created_at'        => $npmedia->created_at,
            'created_by'        => $npmedia->created_by,
            'updated_at'        => $npmedia->updated_at,
            'updated_by'        => $npmedia->updated_by,
        );

        $id = (int)$npmedia->id_npmedia;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getNpmedia($id)) {
                $this->tableGateway->update($data, array('id_npmedia' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteNewspub($id)
    {
        $this->tableGateway->delete(array('id_npmedia' => $id));
    }
}