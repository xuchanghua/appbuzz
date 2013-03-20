<?php
namespace Writer\Form;

use Zend\Form\Form;

class WriterForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('writer');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id_writer',
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
        $this->add(array(
            'name' => 'requirement',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '你希望在新闻撰稿中传达的核心内容是什么',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'hint',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '其他有助于作者更好地撰写稿件的信息',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'web_link',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'web下载链接',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'appstore_link',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Appstore下载链接',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'androidmkt_link',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '安卓市场下载链接',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'MAX_FILE_SIZE',
            'attributes' => array(
                'type' => 'hidden',
                'value' => '100000000'
            ),
        ));
        $this->add(array(
            'name' => 'barcode',
            'attributes' => array(
                'type'  => 'file',
                'placeholder' => '下载二维码',
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