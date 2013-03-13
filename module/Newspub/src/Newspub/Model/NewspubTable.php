<?php
namespace Newspub\Model;

use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;

class NewspubTable
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

    public function getNewspubByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use ($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_newspub DESC');
        });
        return $rowset;
    }

    public function getNewspub($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_newspub' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function saveNewspub(Newspub $newspub)
    {
        $data = array(
            'title'             => $newspub->title,
            'body'              => $newspub->body,
            'download_link'     => $newspub->download_link,
            'appstore_links'    => $newspub->appstore_links,
            'barcode'           => $newspub->barcode,
            'fk_pub_mode'       => $newspub->fk_pub_mode,
            'created_by'        => $newspub->created_by,
            'created_at'        => $newspub->created_at,
            'updated_at'        => $newspub->updated_at,
            'updated_by'        => $newspub->updated_by,
            'fk_newspub_status' => $newspub->fk_newspub_status,
        );

        $id = (int)$newspub->id_newspub;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getNewspub($id)) {
                $this->tableGateway->update($data, array('id_newspub' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteNewspub($id)
    {
        $this->tableGateway->delete(array('id_newspub' => $id));
    }
}