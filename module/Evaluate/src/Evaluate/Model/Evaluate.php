<?php
namespace Evaluate\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Evaluate implements InputFilterAwareInterface
{
    public $id_evaluate;
    public $fk_product;
    public $highlight;
    public $web_link;
    public $appstore_link;
    public $androidmkt_link;
    public $barcode;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_evaluate     = (isset($data['id_evaluate']))     ? $data['id_evaluate']     : null;
        $this->fk_product      = (isset($data['fk_product']))      ? $data['fk_product']      : null;
        $this->highlight       = (isset($data['highlight']))       ? $data['highlight']       : null;
        $this->web_link        = (isset($data['web_link']))        ? $data['web_link']        : null;
        $this->appstore_link   = (isset($data['appstore_link']))   ? $data['appstore_link']   : null;
        $this->androidmkt_link = (isset($data['androidmkt_link'])) ? $data['androidmkt_link'] : null;
        $this->barcode         = (isset($data['barcode']))         ? $data['barcode']         : null;
        $this->created_by      = (isset($data['created_by']))      ? $data['created_by']      : null;
        $this->created_at      = (isset($data['created_at']))      ? $data['created_at']      : null;
        $this->updated_by      = (isset($data['updated_by']))      ? $data['updated_by']      : null;
        $this->updated_at      = (isset($data['updated_at']))      ? $data['updated_at']      : null;
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
                'name'     => 'id_evaluate',
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