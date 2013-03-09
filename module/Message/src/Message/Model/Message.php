<?php
namespace Message\Model;

use Zend\InputFilter\Factory as InputFactory;     // <-- Add this import
use Zend\InputFilter\InputFilter;                 // <-- Add this import
use Zend\InputFilter\InputFilterAwareInterface;   // <-- Add this import
use Zend\InputFilter\InputFilterInterface;        // <-- Add this import

class Message implements InputFilterAwareInterface
{
    public $id_message;
    public $fk_user_from;
    public $subject;
    public $body;
    public $created_at;
    public $updated_at;
    public $fk_message_status;

    protected $inputFilter;                       // <-- Add this variable

    public function exchangeArray($data)
    {
        $this->id_message        = (isset($data['id_message']))        ? $data['id_message']        : null;
        $this->fk_user_from      = (isset($data['fk_user_from']))      ? $data['fk_user_from']      : null;
        $this->subject           = (isset($data['subject']))           ? $data['subject']           : null;
        $this->body              = (isset($data['body']))              ? $data['body']              : null;
        $this->created_at        = (isset($data['created_at']))        ? $data['created_at']        : null;
        $this->updated_at        = (isset($data['updated_at']))        ? $data['updated_at']        : null;
        $this->fk_message_status = (isset($data['fk_message_status'])) ? $data['fk_message_status'] : null;
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
                'name'     => 'id',
                'required' => true,
                'filters'  => array(
                    array('name' => 'Int'),
                ),
            )));

            $inputFilter->add($factory->createInput(array(
                'name'     => 'artist',
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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

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
                            'max'      => 100,
                        ),
                    ),
                ),
            )));

            $this->inputFilter = $inputFilter;
        }

        return $this->inputFilter;
    }
}