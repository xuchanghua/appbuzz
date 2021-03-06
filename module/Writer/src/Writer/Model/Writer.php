<?php
namespace Writer\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Writer implements InputFilterAwareInterface
{
    public $id_writer;
    public $fk_product;
    public $requirement;
    public $hint;
    public $web_link;
    public $appstore_link;
    public $androidmkt_link;
    public $barcode;
    public $fk_writer_status;
    public $created_by;
    public $created_at;
    public $updated_by;
    public $updated_at;
    public $wrtmedia;
    public $order_no;
    public $order_limit;
    public $due_date;
    public $fk_writer_type;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_writer        = (isset($data['id_writer']))        ? $data['id_writer']        : null;
        $this->fk_product       = (isset($data['fk_product']))       ? $data['fk_product']       : null;
        $this->requirement      = (isset($data['requirement']))      ? $data['requirement']      : null;
        $this->hint             = (isset($data['hint']))             ? $data['hint']             : null;
        $this->web_link         = (isset($data['web_link']))         ? $data['web_link']         : null;
        $this->appstore_link    = (isset($data['appstore_link']))    ? $data['appstore_link']    : null;
        $this->androidmkt_link  = (isset($data['androidmkt_link']))  ? $data['androidmkt_link']  : null;
        $this->barcode          = (isset($data['barcode']))          ? $data['barcode']          : null;
        $this->created_by       = (isset($data['created_by']))       ? $data['created_by']       : null;
        $this->created_at       = (isset($data['created_at']))       ? $data['created_at']       : null;
        $this->updated_by       = (isset($data['updated_by']))       ? $data['updated_by']       : null;
        $this->updated_at       = (isset($data['updated_at']))       ? $data['updated_at']       : null;
        $this->wrtmedia         = (isset($data['wrtmedia']))         ? $data['wrtmedia']         : null;
        $this->order_no         = (isset($data['order_no']))         ? $data['order_no']         : null;
        $this->fk_writer_status = (isset($data['fk_writer_status'])) ? $data['fk_writer_status'] : null;
        $this->order_limit      = (isset($data['order_limit']))      ? $data['order_limit']      : null;
        $this->due_date         = (isset($data['due_date']))         ? $data['due_date']         : null;
        $this->fk_writer_type   = (isset($data['fk_writer_type']))   ? $data['fk_writer_type']   : null;
        //leftjoin wrtmedia
        $this->wm_order_no           = (isset($data['wm_order_no']))           ? $data['wm_order_no']           : null;
        $this->wm_fk_enterprise_user = (isset($data['wm_fk_enterprise_user'])) ? $data['wm_fk_enterprise_user'] : null;
        $this->wm_fk_media_user      = (isset($data['wm_fk_media_user']))      ? $data['wm_fk_media_user']      : null;
        $this->wm_created_at         = (isset($data['wm_created_at']))         ? $data['wm_created_at']         : null;
        $this->wm_fk_wrtmedia_status = (isset($data['wm_fk_wrtmedia_status'])) ? $data['wm_fk_wrtmedia_status'] : null;
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
                'name'     => 'id_writer',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            $inputFilter->add($factory->createInput(array(
                'name'     => 'fk_writer_type',
                'required' => false,
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}