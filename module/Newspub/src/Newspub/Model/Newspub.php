<?php
namespace Newspub\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Newspub implements InputFilterAwareInterface
{
    public $id_newspub;
    public $title;
    public $body;
    public $download_link;
    public $appstore_links;
    public $barcode;
    public $fk_pub_mode;
    public $fk_product;
    public $androidmkt_link;
    public $sel_right;
    public $order_no;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_newspub        = (isset($data['id_newspub']))        ? $data['id_newspub']        : null;
        $this->title             = (isset($data['title']))             ? $data['title']             : null;
        $this->body              = (isset($data['body']))              ? $data['body']              : null;
        $this->download_link     = (isset($data['download_link']))     ? $data['download_link']     : null;
        $this->appstore_links    = (isset($data['appstore_links']))    ? $data['appstore_links']    : null;
        $this->barcode           = (isset($data['barcode']))           ? $data['barcode']           : null;
        $this->fk_pub_mode       = (isset($data['fk_pub_mode']))       ? $data['fk_pub_mode']       : null;
        $this->created_by        = (isset($data['created_by']))        ? $data['created_by']        : null;
        $this->created_at        = (isset($data['created_at']))        ? $data['created_at']        : null;
        $this->updated_at        = (isset($data['updated_at']))        ? $data['updated_at']        : null;
        $this->updated_by        = (isset($data['updated_by']))        ? $data['updated_by']        : null;
        $this->fk_newspub_status = (isset($data['fk_newspub_status'])) ? $data['fk_newspub_status'] : null;
        $this->androidmkt_link   = (isset($data['androidmkt_link']))   ? $data['androidmkt_link']   : null;
        $this->fk_product        = (isset($data['fk_product']))        ? $data['fk_product']        : null;
        $this->sel_right         = (isset($data['sel_right']))         ? $data['sel_right']         : null;
        $this->order_no          = (isset($data['order_no']))          ? $data['order_no']          : null;
    }

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
                'name'     => 'id_newspub',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));
            /*$inputFilter->add($factory->createInput(array(
                'name'     => 'sel_right',
                'required' => true,
                'validators' => array(
                    array(
                        'name'     => 'InArray',
                        'options' => array(
                            'haystack' => array(2, 4, 11),
                            'messages' => array(
                                \Zend\Validator\InArray::NOT_IN_ARRAY => 'Please select medias.',
                            ),
                        ),
                    ),
                ),
            )));*/
            $inputFilter->add($factory->createInput(array(
                'name'     => 'title',
                'required' => true,
                'filters'  => array(
                    array('name' => 'StripTags'),
                    array('name' => 'StringTrim'),
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
                'name' => 'fk_product',
                'required' => true,
            )));

            $this->inputFilter = $inputFilter;
        }
        return $this->inputFilter;
    }
}