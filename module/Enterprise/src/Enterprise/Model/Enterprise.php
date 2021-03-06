<?php
namespace Enterprise\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Enterprise implements InputFilterAwareInterface
{
    public $id_enterprise;
    public $name;
    public $location;
    public $address;
    public $invoice_title;
    public $invoice_type;
    public $contacter_name;
    public $contacter_post;
    public $contacter_phone;
    public $contacter_mobile;
    public $contacter_email;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    public $ent_full_name;
    public $tex_registry_number;
    public $bank_name;
    public $bank_account;
    public $ent_address;
    public $ent_phone_number;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->id_enterprise       = (isset($data['id_enterprise']))       ? $data['id_enterprise']       : null;
        $this->name                = (isset($data['name']))                ? $data['name']                : null;
        $this->location            = (isset($data['location']))            ? $data['location']            : null;
        $this->address             = (isset($data['address']))             ? $data['address']             : null;
        $this->invoice_type        = (isset($data['invoice_type']))        ? $data['invoice_type']        : null;
        $this->invoice_title       = (isset($data['invoice_title']))       ? $data['invoice_title']       : null;
        $this->contacter_name      = (isset($data['contacter_name']))      ? $data['contacter_name']      : null;
        $this->contacter_post      = (isset($data['contacter_post']))      ? $data['contacter_post']      : null;
        $this->contacter_phone     = (isset($data['contacter_phone']))     ? $data['contacter_phone']     : null;
        $this->contacter_mobile    = (isset($data['contacter_mobile']))    ? $data['contacter_mobile']    : null;
        $this->contacter_email     = (isset($data['contacter_email']))     ? $data['contacter_email']     : null;
        $this->created_at          = (isset($data['created_at']))          ? $data['created_at']          : null;
        $this->created_by          = (isset($data['created_by']))          ? $data['created_by']          : null;
        $this->updated_at          = (isset($data['updated_at']))          ? $data['updated_at']          : null;
        $this->updated_by          = (isset($data['updated_by']))          ? $data['updated_by']          : null;
        $this->ent_full_name       = (isset($data['ent_full_name']))       ? $data['ent_full_name']       : null;
        $this->tex_registry_number = (isset($data['tex_registry_number'])) ? $data['tex_registry_number'] : null;
        $this->bank_name           = (isset($data['bank_name']))           ? $data['bank_name']           : null;
        $this->bank_account        = (isset($data['bank_account']))        ? $data['bank_account']        : null;
        $this->ent_address         = (isset($data['ent_address']))         ? $data['ent_address']         : null;
        $this->ent_phone_number    = (isset($data['ent_phone_number']))    ? $data['ent_phone_number']    : null;
    }

    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

    public function getInputFilter()
    {
        if (!$this->inputFilter) {
            $inputFilter = new InputFilter();
            $factory     = new InputFactory();

            $inputFilter->add($factory->createInput(array(
                'name'     => 'id_enterprise',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}