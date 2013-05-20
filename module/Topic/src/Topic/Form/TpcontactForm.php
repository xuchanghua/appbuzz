<?php
namespace Topic\Form;

use Zend\Form\Form;

class TpcontactForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('tpcontact');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        
        $this->add(array(
            'name' => 'id_tpcontact',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'fk_product',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        /*$this->add(array(
            'name' => 'matching_degree',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'value_options' => array(
                    '' => '请选择APP产品匹配度',
                    '高关联度' => '高关联度',
                    '低关联度' => '低关联度',
                ),
            ),
        ));*/
        $this->add(array(
            'name' => 'MAX_FILE_SIZE',
            'attributes' => array(
                'type' => 'hidden',
                'value' => '100000000'
            ),
        ));
        $this->add(array(
            'name' => 'attachment',
            'attributes' => array(
                'type'  => 'file',
                'placeholder' => '上传附件',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'topic_link',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => '请填写选题链接',
            ),
        ));
        $this->add(array(
            'name' => 'introduction',
            'attributes' => array(
                'type' => 'textarea',
                'placeholder' => '请输入文字介绍',
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