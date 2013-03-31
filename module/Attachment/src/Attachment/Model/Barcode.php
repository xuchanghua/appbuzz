<?php
namespace Attachment\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Barcode implements InputFilterAwareInterface
{

    public $id_barcode;
    public $filename;
    public $path;
    public $created_by;
    public $created_at;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_barcode = (isset($data['id_barcode'])) ? $data['id_barcode'] : null;
        $this->filename   = (isset($data['filename']))   ? $data['filename']   : null;
        $this->path       = (isset($data['path']))       ? $data['path']       : null;
        $this->created_by = (isset($data['created_by'])) ? $data['created_by'] : null;
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;
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
                'name'     => 'id_barcode',
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