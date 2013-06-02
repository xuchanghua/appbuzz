<?php
namespace Monitor\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Configure implements InputFilterAwareInterface
{
    public $myapp;
    public $myapp2;
    public $myapp3;
    public $competitorapp;
    public $competitorapp2;
    public $competitorapp3;
    public $competitorapp4;
    public $competitorapp5;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->myapp          = (isset($data['myapp']))          ? $data['myapp']          : null;
        $this->myapp2         = (isset($data['myapp2']))         ? $data['myapp2']         : null;
        $this->myapp3         = (isset($data['myapp3']))         ? $data['myapp3']         : null;
        $this->competitorapp  = (isset($data['competitorapp']))  ? $data['competitorapp']  : null;
        $this->competitorapp2 = (isset($data['competitorapp2'])) ? $data['competitorapp2'] : null;
        $this->competitorapp3 = (isset($data['competitorapp3'])) ? $data['competitorapp3'] : null;
        $this->competitorapp4 = (isset($data['competitorapp4'])) ? $data['competitorapp4'] : null;
        $this->competitorapp5 = (isset($data['competitorapp5'])) ? $data['competitorapp5'] : null;
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
                'name'     => 'myapp',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'myapp2',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'myapp3',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'competitorapp',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'competitorapp2',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'competitorapp3',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'competitorapp4',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
                            'max'      => 255,
                        ),
                    ),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'competitorapp5',
                'filters'  => array(
                ),
                'validators' => array(
                    array(
                        'name'    => 'StringLength',
                        'options' => array(
                            'encoding' => 'UTF-8',
                            'min'      => 0,
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