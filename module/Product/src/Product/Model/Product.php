<?php
namespace Product\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Product implements InputFilterAwareInterface
{

    public $id_product;
    public $name;
    public $type;
    public $appstore_link;
    public $androidmkt_link;
    public $web_link;
    public $barcode;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $description;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_product      = (isset($data['id_product']))      ? $data['id_product']      : null;
        $this->name            = (isset($data['name']))            ? $data['name']            : null;
        $this->type            = (isset($data['type']))            ? $data['type']            : null;
        $this->appstore_link   = (isset($data['appstore_link']))   ? $data['appstore_link']   : null;
        $this->androidmkt_link = (isset($data['androidmkt_link'])) ? $data['androidmkt_link'] : null;
        $this->web_link        = (isset($data['web_link']))        ? $data['web_link']        : null;
        $this->barcode         = (isset($data['barcode']))         ? $data['barcode']         : null;
        $this->created_by      = (isset($data['created_by']))      ? $data['created_by']      : null;
        $this->created_at      = (isset($data['created_at']))      ? $data['created_at']      : null;
        $this->updated_by      = (isset($data['updated_by']))      ? $data['updated_by']      : null;
        $this->updated_at      = (isset($data['updated_at']))      ? $data['updated_at']      : null;
        $this->description     = (isset($data['description']))     ? $data['description']     : null;
    }

    // Add the following method:
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

    // Add content to this method:
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
                'name'     => 'id_product',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'name',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'type',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 1,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}