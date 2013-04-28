<?php
namespace Article\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Banner implements InputFilterAwareInterface
{

    public $id_banner;
    public $filename;
    public $path;
    public $url;
    public $created_at;
    public $created_by;
    public $updated_at;
    public $updated_by;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_banner  = (isset($data['id_banner']))  ? $data['id_banner']  : null;
        $this->filename   = (isset($data['filename']))   ? $data['filename']   : null;
        $this->path       = (isset($data['path']))       ? $data['path']       : null;
        $this->url        = (isset($data['url']))        ? $data['url']        : null;
        $this->created_at = (isset($data['created_at'])) ? $data['created_at'] : null;
        $this->created_by = (isset($data['created_by'])) ? $data['created_by'] : null;
        $this->updated_at = (isset($data['updated_at'])) ? $data['updated_at'] : null;
        $this->updated_by = (isset($data['updated_by'])) ? $data['updated_by'] : null;
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
                'name'     => 'id_banner',
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