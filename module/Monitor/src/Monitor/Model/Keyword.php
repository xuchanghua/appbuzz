<?php
namespace Monitor\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Keyword implements InputFilterAwareInterface
{

    public $id_keyword;
    public $fk_monitor;
    public $keyword;
    public $fk_keyword_type;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_keyword      = (isset($data['id_keyword']))      ? $data['id_keyword']      : null;
        $this->fk_monitor      = (isset($data['fk_monitor']))      ? $data['fk_monitor']      : null;
        $this->keyword         = (isset($data['keyword']))         ? $data['keyword']         : null;
        $this->fk_keyword_type = (isset($data['fk_keyword_type'])) ? $data['fk_keyword_type'] : null;
        $this->created_at      = (isset($data['created_at']))      ? $data['created_at']      : null;
        $this->created_by      = (isset($data['created_by']))      ? $data['created_by']      : null;
        $this->updated_at      = (isset($data['updated_at']))      ? $data['updated_at']      : null;
        $this->updated_by      = (isset($data['updated_by']))      ? $data['updated_by']      : null;
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
                'name'     => 'id_keyword',
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