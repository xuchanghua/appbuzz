<?php
namespace Interview\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Interview implements InputFilterAwareInterface
{
    public $id_interview;
    public $fk_product;
    public $fk_enterprise_user;
    public $fk_media_user;
    public $date_time;
    public $purpose;
    public $outline;
    public $fk_interview_status;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    public $order_no;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_interview        = (isset($data['id_interview']))        ? $data['id_interview']        : null;
        $this->fk_product          = (isset($data['fk_product']))          ? $data['fk_product']          : null;
        $this->fk_enterprise_user  = (isset($data['fk_enterprise_user']))  ? $data['fk_enterprise_user']  : null;
        $this->fk_media_user       = (isset($data['fk_media_user']))       ? $data['fk_media_user']       : null;
        $this->date_time           = (isset($data['date_time']))           ? $data['date_time']           : null;
        $this->purpose             = (isset($data['purpose']))             ? $data['purpose']             : null;
        $this->outline             = (isset($data['outline']))             ? $data['outline']             : null;
        $this->fk_interview_status = (isset($data['fk_interview_status'])) ? $data['fk_interview_status'] : null;
        $this->created_at          = (isset($data['created_at']))          ? $data['created_at']          : null;
        $this->created_by          = (isset($data['created_by']))          ? $data['created_by']          : null;
        $this->updated_at          = (isset($data['updated_at']))          ? $data['updated_at']          : null;
        $this->updated_by          = (isset($data['updated_by']))          ? $data['updated_by']          : null;
        $this->order_no            = (isset($data['order_no']))            ? $data['order_no']            : null;
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
                'name'     => 'id_interview',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'=> 'date_time',
                'validators' => array(
                    array(
                        'name' => 'Date',
                        'format' => 'YYYY-MM-DD',
                        //'format' => 'Y-m-d H:i:s'
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}