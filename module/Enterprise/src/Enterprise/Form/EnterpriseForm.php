<?php
namespace Enterprise\Form;

use Zend\Form\Form;

class EnterpriseForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('enterprise');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(array(
            'name' => 'id_enterprise',
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
            'type'  => 'Zend\Form\Element\Select',
            'options' => array(
                'label' => '',
                'value_options' => array(
                    '' => '请选择发票类型',
                    '1'   => '普通发票',
                    '2' => '增值税发票',
                ),
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
            'name' => 'contacter_mobile',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '手机',
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
            'name' => 'ent_full_name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '公司全称',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'tex_registry_number',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '税务登记证号',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'bank_name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '公司银行开户行名称',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'bank_account',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '公司银行账号',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'ent_address',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '公司地址',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'ent_phone_number',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '公司电话',
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