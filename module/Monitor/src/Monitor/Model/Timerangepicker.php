<?php
namespace Monitor\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Timerangepicker implements InputFilterAwareInterface
{
    public $start_date;
    public $end_date;
    protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->start_date = (isset($data['start_date'])) ? $data['start_date'] : null;
        $this->end_date   = (isset($data['end_date']))   ? $data['end_date']   : null;
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
                'name'     => 'start_date',
                //'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Date',
                        'format' => 'YYYY-MM-DD',
                    ),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'end_date',
                //'required' => true,
                'validators' => array(
                    array(
                        'name' => 'Date',
                        'format' => 'YYYY-MM-DD',
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}