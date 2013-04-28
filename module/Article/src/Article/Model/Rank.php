<?php
namespace Article\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Rank implements InputFilterAwareInterface
{

    public $id_rank;
    public $name;
    public $description;
    public $obj_type;
    public $fk_no_1;
    public $fk_no_2;
    public $fk_no_3;
    public $fk_no_4;
    public $fk_no_5;
    public $fk_no_6;
    public $fk_no_7;
    public $fk_no_8;
    public $fk_no_9;
    public $fk_no_10;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_rank     = (isset($data['id_rank']))     ? $data['id_rank']     : null;
        $this->name        = (isset($data['name']))        ? $data['name']        : null;
        $this->description = (isset($data['description'])) ? $data['description'] : null;
        $this->obj_type    = (isset($data['obj_type']))    ? $data['obj_type']    : null;
        $this->fk_no_1     = (isset($data['fk_no_1']))     ? $data['fk_no_1']     : null;
        $this->fk_no_2     = (isset($data['fk_no_2']))     ? $data['fk_no_2']     : null;
        $this->fk_no_3     = (isset($data['fk_no_3']))     ? $data['fk_no_3']     : null;
        $this->fk_no_4     = (isset($data['fk_no_4']))     ? $data['fk_no_4']     : null;
        $this->fk_no_5     = (isset($data['fk_no_5']))     ? $data['fk_no_5']     : null;
        $this->fk_no_6     = (isset($data['fk_no_6']))     ? $data['fk_no_6']     : null;
        $this->fk_no_7     = (isset($data['fk_no_7']))     ? $data['fk_no_7']     : null;
        $this->fk_no_8     = (isset($data['fk_no_8']))     ? $data['fk_no_8']     : null;
        $this->fk_no_9     = (isset($data['fk_no_9']))     ? $data['fk_no_9']     : null;
        $this->fk_no_10    = (isset($data['fk_no_10']))    ? $data['fk_no_10']    : null;
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
                'name'     => 'id_rank',
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