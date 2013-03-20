<?php
namespace Writer\Model;

use Zend\Db\TableGateway\TableGateway;   
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

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

    public function fetchWriterByUser($created_by)
    {
        $rowset = $this->tableGateway->select(function(Select $select) use($created_by){
            $select->where->equalTo('created_by', $created_by);
            $select->order('id_writer DESC');
        });
        return $rowset;
    }

    public function getWriter($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_writer' => $id));
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
            throw new \Exception("Could not find row $created_at, $created_by");
        }
        return $row->id_writer;
    }

    public function saveWriter(Writer $writer)
    {
        $data = array(
            'fk_product'      => $writer->fk_product,
            'requirement'     => $writer->requirement,
            'hint'            => $writer->hint,
            'web_link'        => $writer->web_link,
            'appstore_link'   => $writer->appstore_link,
            'androidmkt_link' => $writer->androidmkt_link,
            'barcode'         => $writer->barcode,
            'created_by'      => $writer->created_by,
            'created_at'      => $writer->created_at,
            'updated_by'      => $writer->updated_by,
            'updated_at'      => $writer->updated_at,
        );

        $id = (int)$writer->id_writer;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getWriter($id)) {
                $this->tableGateway->update($data, array('id_writer' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteWriter($id)
    {
        $this->tableGateway->delete(array('id_writer' => $id));
    }
}