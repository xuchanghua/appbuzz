<?php
namespace Topic\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Tpcontact implements InputFilterAwareInterface
{
    public $id_tpcontact;
    public $fk_topic;
    public $fk_enterprise_user;
    public $fk_media_user;
    public $fk_product;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $order_no;
    public $matching_degree;
    public $fk_tpcontact_status;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_tpcontact        = (isset($data['id_tpcontact']))        ? $data['id_tpcontact']        : null;
        $this->fk_topic            = (isset($data['fk_topic']))            ? $data['fk_topic']            : null;
        $this->fk_enterprise_user  = (isset($data['fk_enterprise_user']))  ? $data['fk_enterprise_user']  : null;
        $this->fk_media_user       = (isset($data['fk_media_user']))       ? $data['fk_media_user']       : null;
        $this->fk_product          = (isset($data['fk_product']))          ? $data['fk_product']          : null;
        $this->created_by          = (isset($data['created_by']))          ? $data['created_by']          : null;
        $this->created_at          = (isset($data['created_at']))          ? $data['created_at']          : null;
        $this->updated_by          = (isset($data['updated_by']))          ? $data['updated_by']          : null;
        $this->updated_at          = (isset($data['updated_at']))          ? $data['updated_at']          : null;
        $this->order_no            = (isset($data['order_no']))            ? $data['order_no']            : null;
        $this->matching_degree     = (isset($data['matching_degree']))     ? $data['matching_degree']     : null;
        $this->fk_tpcontact_status = (isset($data['fk_tpcontact_status'])) ? $data['fk_tpcontact_status'] : null;
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
                'name'     => 'id_tpcontact',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'matching_degree',
                'required' => true,
                'filters'  => array(
                    //array('name' => 'Int'),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}