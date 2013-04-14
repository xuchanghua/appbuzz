<?php
namespace Media\Model;

use Zend\Db\TableGateway\TableGateway;  

class MediaTable
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

    public function getMedia($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_media' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getMediaByName($name)
    {
        $rowset = $this->tableGateway->select(array('name' => $name));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $name");
        }
        return $row;
    }

    public function saveMedia(Media $media)
    {
        $data = array(
            'name'            => $media->name,
            'location'        => $media->location,
            'address'         => $media->address,
            'invoice_title'   => $media->invoice_title,
            'invoice_type'    => $media->invoice_type,
            'contacter_name'  => $media->contacter_name,
            'contacter_post'  => $media->contacter_post,
            'contacter_phone' => $media->contacter_phone,
            'contacter_email' => $media->contacter_email,
            'created_at'      => $media->created_at,
            'created_by'      => $media->created_by,
            'updated_at'      => $media->updated_at,
            'updated_by'      => $media->updated_by,
        );

        $id = (int)$media->id_media;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getMedia($id)) {
                $this->tableGateway->update($data, array('id_media' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteMedia($id)
    {
        $this->tableGateway->delete(array('id_media' => $id));
    }

}