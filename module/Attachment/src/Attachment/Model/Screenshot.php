<?php
namespace Attachment\Model;

use Zend\InputFilter\Factory as InputFactory;     
use Zend\InputFilter\InputFilter;                 
use Zend\InputFilter\InputFilterAwareInterface;   
use Zend\InputFilter\InputFilterInterface;  

class Screenshot implements InputFilterAwareInterface
{

    public $id_screenshot;
    public $filename;
    public $path;
    public $fk_evaluate;
    public $fk_writer;
    public $fk_interview;
    public $created_by;
    public $created_at;
    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_screenshot = (isset($data['id_screenshot'])) ? $data['id_screenshot'] : null;
        $this->filename      = (isset($data['filename']))      ? $data['filename']      : null;
        $this->path          = (isset($data['path']))          ? $data['path']          : null;
        $this->fk_evaluate   = (isset($data['fk_evaluate']))   ? $data['fk_evaluate']   : null;
        $this->fk_writer     = (isset($data['fk_writer']))     ? $data['fk_writer']     : null;
        $this->fk_interview  = (isset($data['fk_interview']))  ? $data['fk_interview']  : null; 
        $this->created_by    = (isset($data['created_by']))    ? $data['created_by']    : null;
        $this->created_at    = (isset($data['created_at']))    ? $data['created_at']    : null;
        //count
        $this->count_ss      = (isset($data['count_ss']))      ? $data['count_ss']      : null;
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
                'name'     => 'id_screenshot',
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