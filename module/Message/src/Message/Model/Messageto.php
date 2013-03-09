<?php
namespace Message\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class Messageto implements InputFilterAwareInterface
{
    public $id_message_to;
    public $fk_message;
    public $fk_user_to;

    public function exchangeArray($data)
    {
        $this->id_message_to = (isset($data['id_message_to'])) ? $data['id_message_to'] : null;
        $this->fk_message    = (isset($data['fk_message']))    ? $data['fk_message']    : null;
        $this->fk_user_to    = (isset($data['fk_user_to']))    ? $data['fk_user_to']    : null;
    }
}