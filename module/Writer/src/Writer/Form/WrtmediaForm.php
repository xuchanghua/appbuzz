<?php
namespace Writer\Form;

use Zend\Form\Form;

class WrtmediaForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('wrtmedia');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id_wrtmedia',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'first_draft_title',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '标题',
            ),
        ));
        $this->add(array(
            'name' => 'first_draft_body',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '内容',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'second_draft_title',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '标题',
            ),
        ));
        $this->add(array(
            'name' => 'second_draft_body',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '内容',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'revision_suggestion',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '修改意见',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
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