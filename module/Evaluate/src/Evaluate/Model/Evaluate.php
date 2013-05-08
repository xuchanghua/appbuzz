<?php
namespace Evaluate\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Evaluate implements InputFilterAwareInterface
{
    public $id_evaluate;
    public $fk_product;
    public $highlight;
    public $web_link;
    public $appstore_link;
    public $androidmkt_link;
    public $barcode;
    public $evamedia;
    public $requirement;
    public $due_date;
    public $order_no;
    public $order_limit;
    public $fk_evaluate_status;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_evaluate        = (isset($data['id_evaluate']))        ? $data['id_evaluate']        : null;
        $this->fk_product         = (isset($data['fk_product']))         ? $data['fk_product']         : null;
        $this->highlight          = (isset($data['highlight']))          ? $data['highlight']          : null;
        $this->web_link           = (isset($data['web_link']))           ? $data['web_link']           : null;
        $this->appstore_link      = (isset($data['appstore_link']))      ? $data['appstore_link']      : null;
        $this->androidmkt_link    = (isset($data['androidmkt_link']))    ? $data['androidmkt_link']    : null;
        $this->barcode            = (isset($data['barcode']))            ? $data['barcode']            : null;
        $this->created_by         = (isset($data['created_by']))         ? $data['created_by']         : null;
        $this->created_at         = (isset($data['created_at']))         ? $data['created_at']         : null;
        $this->updated_by         = (isset($data['updated_by']))         ? $data['updated_by']         : null;
        $this->updated_at         = (isset($data['updated_at']))         ? $data['updated_at']         : null;
        $this->evamedia           = (isset($data['evamedia']))           ? $data['evamedia']           : null;
        $this->requirement        = (isset($data['requirement']))        ? $data['requirement']        : null;
        $this->due_date           = (isset($data['due_date']))           ? $data['due_date']           : null;
        $this->order_no           = (isset($data['order_no']))           ? $data['order_no']           : null;
        $this->order_limit        = (isset($data['order_limit']))        ? $data['order_limit']        : null;
        $this->fk_evaluate_status = (isset($data['fk_evaluate_status'])) ? $data['fk_evaluate_status'] : null;
        // leftjoin evamedia
        $this->em_order_no           = (isset($data['em_order_no']))           ? $data['em_order_no']           : null;
        $this->em_fk_enterprise_user = (isset($data['em_fk_enterprise_user'])) ? $data['em_fk_enterprise_user'] : null;
        $this->em_fk_media_user      = (isset($data['em_fk_media_user']))      ? $data['em_fk_media_user']      : null;
        $this->em_created_at         = (isset($data['em_created_at']))         ? $data['em_created_at']         : null;
        $this->em_fk_evamedia_status = (isset($data['em_fk_evamedia_status'])) ? $data['em_fk_evamedia_status'] : null;        
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
                'name'     => 'id_evaluate',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'due_date',
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