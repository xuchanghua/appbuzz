<?php
namespace Article\Form;

use Zend\Form\Form;

class BannerForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('banner');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(array(
            'name' => 'id_banner',
            'attributes' => array(
                'type'  => 'hidden',
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
            'name' => 'filename',
            'attributes' => array(
                'type'  => 'file',
                'placeholder' => '横幅名称',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'url',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '横幅链接地址',
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