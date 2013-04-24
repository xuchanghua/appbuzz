<?php
namespace Evaluate\Form;

use Zend\Form\Form;

class EvaluateForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('evaluate');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id_evaluate',
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
            'name' => 'highlight',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '产品卖点',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'requirement',
            'attributes' => array(
                'type' => 'textarea',
                'placeholder' => '评测要求',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'due_date',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'YYYY-MM-DD',
                'id' => 'f_date_ETA',
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
                'id' => 'web_link',
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
                'id' => 'appstore_link',
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
                'id' => 'androidmkt_link',
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
            'name' => 'evamedia',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '输入媒体用户，用分号";"分隔',
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