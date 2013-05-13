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
    public $q1;
    public $a1;
    public $q2;
    public $a2;
    public $q3;
    public $a3;
    public $q4;
    public $a4;
    public $q5;
    public $a5;
    public $q6;
    public $a6;
    public $q7;
    public $a7;
    public $q8;
    public $a8;
    public $q9;
    public $a9;
    public $q10;
    public $a10;
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
        $this->q1                  = (isset($data['q1']))                  ? $data['q1']                  : null;
        $this->a1                  = (isset($data['a1']))                  ? $data['a1']                  : null;
        $this->q2                  = (isset($data['q2']))                  ? $data['q2']                  : null;
        $this->a2                  = (isset($data['a2']))                  ? $data['a2']                  : null;
        $this->q3                  = (isset($data['q3']))                  ? $data['q3']                  : null;
        $this->a3                  = (isset($data['a3']))                  ? $data['a3']                  : null;
        $this->q4                  = (isset($data['q4']))                  ? $data['q4']                  : null;
        $this->a4                  = (isset($data['a4']))                  ? $data['a4']                  : null;
        $this->q5                  = (isset($data['q5']))                  ? $data['q5']                  : null;
        $this->a5                  = (isset($data['a5']))                  ? $data['a5']                  : null;
        $this->q6                  = (isset($data['q6']))                  ? $data['q6']                  : null;
        $this->a6                  = (isset($data['a6']))                  ? $data['a6']                  : null;
        $this->q7                  = (isset($data['q7']))                  ? $data['q7']                  : null;
        $this->a7                  = (isset($data['a7']))                  ? $data['a7']                  : null;
        $this->q8                  = (isset($data['q8']))                  ? $data['q8']                  : null;
        $this->a8                  = (isset($data['a8']))                  ? $data['a8']                  : null;
        $this->q9                  = (isset($data['q9']))                  ? $data['q9']                  : null;
        $this->a9                  = (isset($data['a9']))                  ? $data['a9']                  : null;
        $this->q10                 = (isset($data['q10']))                 ? $data['q10']                 : null;
        $this->a10                 = (isset($data['a10']))                 ? $data['a10']                 : null;
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
            /*$inputFilter->add($factory->createInput(array(
                'name'=> 'date_time',
                'validators' => array(
                    array(
                        'name' => 'Date',
                        'format' => 'YYYY-MM-DD',
                        //'format' => 'Y-m-d H:i:s'
                    ),
                ),
            )));*/

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}