<?php
namespace Message\Form;

use Zend\Form\Form;

class MessageForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('message');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id_message',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'from',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'to',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '收件人',
            ),
        ));
        $this->add(array(
            'name' => 'cc',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '抄送',
            ),
        ));
        $this->add(array(
            'name' => 'bcc',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '密送',
            ),
        ));
        $this->add(array(
            'name' => 'subject',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '主题',
            ),
        ));
        $this->add(array(
            'name' => 'body',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '正文',
                'rows'        => 17,       
            ),
        ));
        $this->add(array(
            'name' => 'fk_message_status',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}