<?php
namespace Media\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Media implements InputFilterAwareInterface
{
    public $id_media;
    public $name;
    public $location;
    public $address;
    public $invoice_title;
    public $invoice_type;
    public $contacter_name;
    public $contacter_post;
    public $contacter_phone;
    public $contacter_email;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;

    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->id_media        = (isset($data['id_media']))        ? $data['id_media']        : null;
        $this->name            = (isset($data['name']))            ? $data['name']            : null;
        $this->location        = (isset($data['location']))        ? $data['location']        : null;
        $this->address         = (isset($data['address']))         ? $data['address']         : null;
        $this->invoice_type    = (isset($data['invoice_type']))    ? $data['invoice_type']    : null;
        $this->invoice_title   = (isset($data['invoice_title']))   ? $data['invoice_title']   : null;
        $this->contacter_name  = (isset($data['contacter_name']))  ? $data['contacter_name']  : null;
        $this->contacter_post  = (isset($data['contacter_post']))  ? $data['contacter_post']  : null;
        $this->contacter_phone = (isset($data['contacter_phone'])) ? $data['contacter_phone'] : null;
        $this->contacter_email = (isset($data['contacter_email'])) ? $data['contacter_email'] : null;
        $this->created_at      = (isset($data['created_at']))      ? $data['created_at']      : null;
        $this->created_by      = (isset($data['created_by']))      ? $data['created_by']      : null;
        $this->updated_at      = (isset($data['updated_at']))      ? $data['updated_at']      : null;
        $this->updated_by      = (isset($data['updated_by']))      ? $data['updated_by']      : null;
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
                'name'     => 'id_media',
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