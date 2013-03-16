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
    public $contacter_email;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->id_enterprise   = (isset($data['id_enterprise']))   ? $data['id_enterprise']   : null;
        $this->name            = (isset($data['name']))            ? $data['name']            : null;
        $this->location        = (isset($data['location']))        ? $data['location']        : null;
        $this->address         = (isset($data['address']))         ? $data['address']         : null;
        $this->invoice_type    = (isset($data['invoice_type']))    ? $data['invoice_type']    : null;
        $this->invoice_title   = (isset($data['invoice_title']))   ? $data['invoice_title']   : null;
        $this->contacter_name  = (isset($data['contacter_name']))  ? $data['contacter_name']  : null;
        $this->contacter_post  = (isset($data['contacter_post']))  ? $data['contacter_post']  : null;
        $this->contacter_phone = (isset($data['contacter_phone'])) ? $data['contacter_phone'] : null;
        $this->contacter_email = (isset($data['contacter_email'])) ? $data['contacter_email'] : null;
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