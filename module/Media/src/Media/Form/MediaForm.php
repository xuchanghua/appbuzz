<?php
namespace Media\Form;

use Zend\Form\Form;

class MediaForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('media');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(array(
            'name' => 'id_media',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '公司名称',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'location',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '所在地',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'address',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '办公地址',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'invoice_title',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '发票抬头',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'invoice_type',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '发票类型',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'contacter_name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '姓名',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'contacter_post',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '职位',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'contacter_phone',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '电话',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'contacter_email',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '邮箱',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type' => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary',
            ),
        ));
    }
}