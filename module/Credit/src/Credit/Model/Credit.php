<?php
namespace Credit\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Credit implements InputFilterAwareInterface
{

    public $id_credit;
    public $fk_user;
    public $fk_user_type;
    public $amount;
    public $created_at;
    public $created_by;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_credit    = (isset($data['id_credit']))    ? $data['id_credit']    : null;
        $this->fk_user      = (isset($data['fk_user']))      ? $data['fk_user']      : null;
        $this->fk_user_type = (isset($data['fk_user_type'])) ? $data['fk_user_type'] : null;
        $this->amount       = (isset($data['amount']))       ? $data['amount']       : null;
        $this->created_at   = (isset($data['created_at']))   ? $data['created_at']   : null;
        $this->created_by   = (isset($data['created_by']))   ? $data['created_by']   : null;
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
                'name'     => 'id_credit',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'amount',
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