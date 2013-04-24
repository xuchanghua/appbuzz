<?php
namespace Topic\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Topic implements InputFilterAwareInterface
{
    public $id_topic;
    public $topic_type;
    public $abstract;
    public $app_type;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $due_date;
    public $order_no;
    public $fk_topic_status;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_topic        = (isset($data['id_topic']))        ? $data['id_topic']        : null;
        $this->topic_type      = (isset($data['topic_type']))      ? $data['topic_type']      : null;
        $this->abstract        = (isset($data['abstract']))        ? $data['abstract']        : null;
        $this->app_type        = (isset($data['app_type']))        ? $data['app_type']        : null;
        $this->created_by      = (isset($data['created_by']))      ? $data['created_by']      : null;
        $this->created_at      = (isset($data['created_at']))      ? $data['created_at']      : null;
        $this->updated_by      = (isset($data['updated_by']))      ? $data['updated_by']      : null;
        $this->updated_at      = (isset($data['updated_at']))      ? $data['updated_at']      : null;
        $this->due_date        = (isset($data['due_date']))        ? $data['due_date']        : null;
        $this->fk_topic_status = (isset($data['fk_topic_status'])) ? $data['fk_topic_status'] : null;
        $this->order_no        = (isset($data['order_no']))        ? $data['order_no']        : null;
        // leftjoin tpcontact
        $this->tc_order_no            = (isset($data['tc_order_no']))            ? $data['tc_order_no']            : null;
        $this->tc_fk_enterprise_user  = (isset($data['tc_fk_enterprise_user']))  ? $data['tc_fk_enterprise_user']  : null;
        $this->tc_fk_media_user       = (isset($data['tc_fk_media_user']))       ? $data['tc_fk_media_user']       : null;
        $this->tc_created_at          = (isset($data['tc_created_at']))          ? $data['tc_created_at']          : null;
        $this->tc_fk_tpcontact_status = (isset($data['tc_fk_tpcontact_status'])) ? $data['tc_fk_tpcontact_status'] : null;
        $this->tc_fk_product          = (isset($data['tc_fk_product']))          ? $data['tc_fk_product']          : null;
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
                'name'     => 'id_topic',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'topic_type',
                'required' => true,
                'filters'  => array(
                    //array('name' => 'StripTags'),
                    //array('name' => 'StringTrim'),
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
                'name'     => 'due_date',
                'required' => true,
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