<?php
namespace Credit\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Creditlog implements InputFilterAwareInterface
{

    public $id_creditlog;
    public $fk_credit;
    public $fk_service_type;
    public $fk_from;
    public $fk_to;
    public $date_time;
    public $amount;
    public $is_pay;
    public $is_charge;
    public $order_no;
    public $created_at;
    public $created_by;
    public $remaining_balance;
    public $deposit;
    public $remaining_deposit;
    public $is_pay_deposit;
    public $is_charge_deposit;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_creditlog      = (isset($data['id_creditlog']))      ? $data['id_creditlog']      : null;
        $this->fk_credit         = (isset($data['fk_credit']))         ? $data['fk_credit']         : null;
        $this->fk_service_type   = (isset($data['fk_service_type']))   ? $data['fk_service_type']   : null;
        $this->fk_from           = (isset($data['fk_from']))           ? $data['fk_from']           : null;
        $this->fk_to             = (isset($data['fk_to']))             ? $data['fk_to']             : null;
        $this->date_time         = (isset($data['date_time']))         ? $data['date_time']         : null;
        $this->amount            = (isset($data['amount']))            ? $data['amount']            : null;
        $this->is_pay            = (isset($data['is_pay']))            ? $data['is_pay']            : null;
        $this->is_charge         = (isset($data['is_charge']))         ? $data['is_charge']         : null;
        $this->order_no          = (isset($data['order_no']))          ? $data['order_no']          : null;
        $this->created_at        = (isset($data['created_at']))        ? $data['created_at']        : null;
        $this->created_by        = (isset($data['created_by']))        ? $data['created_by']        : null;
        $this->remaining_balance = (isset($data['remaining_balance'])) ? $data['remaining_balance'] : null;
        $this->deposit           = (isset($data['deposit']))           ? $data['deposit']           : null;
        $this->remaining_deposit = (isset($data['remaining_deposit'])) ? $data['remaining_deposit'] : null;
        $this->is_pay_deposit    = (isset($data['is_pay_deposit']))    ? $data['is_pay_deposit']    : null;
        $this->is_charge_deposit = (isset($data['is_charge_deposit'])) ? $data['is_charge_deposit'] : null;
        //join
        $this->st_description  = (isset($data['st_description']))  ? $data['st_description']  : null;
        $this->u1_username     = (isset($data['u1_username']))     ? $data['u1_username']     : null;
        $this->u2_username     = (isset($data['u2_username']))     ? $data['u2_username']     : null;
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
                'name'     => 'id_creditlog',
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
            $inputFilter->add($factory->createInput(array(
                'name'     => 'fk_credit',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'fk_service_type',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'fk_from',
                //'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'fk_to',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'is_pay',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'is_charge',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'remaining_balance',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'deposit',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'remaining_deposit',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'is_pay_deposit',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'is_charge_deposit',
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