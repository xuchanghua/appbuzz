<?php
namespace Enterprise\Model;

use Zend\Db\TableGateway\TableGateway;  

class EnterpriseTable
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

    public function getEnterprise($id)
    {
        $id  = (int) $id;
        $rowset = $this->tableGateway->select(array('id_enterprise' => $id));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $id");
        }
        return $row;
    }

    public function getEnterpriseByName($name)
    {
        $rowset = $this->tableGateway->select(array('name' => $name));
        $row = $rowset->current();
        if (!$row) {
            throw new \Exception("Could not find row $name");
        }
        return $row;
    }

    public function saveEnterprise(Enterprise $enterprise)
    {
        $data = array(
            'name'                => $enterprise->name,
            'location'            => $enterprise->location,
            'address'             => $enterprise->address,
            'invoice_title'       => $enterprise->invoice_title,
            'invoice_type'        => $enterprise->invoice_type,
            'contacter_name'      => $enterprise->contacter_name,
            'contacter_post'      => $enterprise->contacter_post,
            'contacter_phone'     => $enterprise->contacter_phone,
            'contacter_mobile'    => $enterprise->contacter_mobile,
            'contacter_email'     => $enterprise->contacter_email,
            'created_at'          => $enterprise->created_at,
            'created_by'          => $enterprise->created_by,
            'updated_at'          => $enterprise->updated_at,
            'updated_by'          => $enterprise->updated_by,
            'ent_full_name'       => $enterprise->ent_full_name,
            'tex_registry_number' => $enterprise->tex_registry_number,
            'bank_name'           => $enterprise->bank_name,
            'bank_account'        => $enterprise->bank_account,
            'ent_address'         => $enterprise->ent_address,
            'ent_phone_number'    => $enterprise->ent_phone_number,
        );

        $id = (int)$enterprise->id_enterprise;
        if ($id == 0) {
            $this->tableGateway->insert($data);
        } else {
            if ($this->getEnterprise($id)) {
                $this->tableGateway->update($data, array('id_enterprise' => $id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteEnterprise($id)
    {
        $this->tableGateway->delete(array('id_enterprise' => $id));
    }

}