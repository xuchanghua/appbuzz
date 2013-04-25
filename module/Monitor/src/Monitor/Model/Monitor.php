<?php
namespace Monitor\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Monitor implements InputFilterAwareInterface
{

    public $id_monitor;
    public $fk_enterprise_user;
    public $duration;
    public $start_date;
    public $end_date;
    public $order_no;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_monitor         = (isset($data['id_monitor']))         ? $data['id_monitor']         : null;
        $this->fk_enterprise_user = (isset($data['fk_enterprise_user'])) ? $data['fk_enterprise_user'] : null;
        $this->duration           = (isset($data['duration']))           ? $data['duration']           : null;
        $this->start_date         = (isset($data['start_date']))         ? $data['start_date']         : null;
        $this->end_date           = (isset($data['end_date']))           ? $data['end_date']           : null;
        $this->order_no           = (isset($data['order_no']))           ? $data['order_no']           : null;
        $this->created_at         = (isset($data['created_at']))         ? $data['created_at']         : null;
        $this->created_by         = (isset($data['created_by']))         ? $data['created_by']         : null;
        $this->updated_at         = (isset($data['updated_at']))         ? $data['updated_at']         : null;
        $this->updated_by         = (isset($data['updated_by']))         ? $data['updated_by']         : null;
        $this->kw_keyword         = (isset($data['kw_keyword']))         ? $data['kw_keyword']         : null;
        $this->kw_fk_keyword_type = (isset($data['kw_fk_keyword_type'])) ? $data['kw_fk_keyword_type'] : null;
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
                'name'     => 'id_monitor',
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