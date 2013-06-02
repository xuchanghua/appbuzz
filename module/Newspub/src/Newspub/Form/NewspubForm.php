<?php
namespace Newspub\Form;

use Zend\Form\Form;

class NewspubForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('newspub');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id_newspub',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'fk_product',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '标题',
                'id' => 'title',
                'maxlength' => 30,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'body',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '正文',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'download_link',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'web下载链接',
                'id' => 'download_link',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'appstore_links',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => 'Appstore下载链接',
                'id' => 'appstore_links',
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
                'id' => 'barcode',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        /*$this->add(array(
            'name' => 'fk_pub_mode',
            'type'  => 'Zend\Form\Element\Radio',
            'options' => array(
                'value_options' => array(
                    '1' => '单篇发布',
                    '2' => '打包发布',
                    ),
                ),
            ));*/
        $this->add(array(
            'name' => 'fk_pub_mode',
            'type'  => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'select_pub_mode',
                'class' => 'select_pub_mode',//class must be defined otherwise id doesn't work in the view......
                ),
            'options' => array(
                'value_options' => array(
                    array('id' => 'option1', 'class' => 'option1', 'value' => '1', 'label' => '按媒体单篇发布，300 元/篇： 发布 0 篇，共 0 元。'),
                    array('id' => 'option2', 'class' => 'option2', 'value' => '2', 'label' => '打包发布，实发篇数不少于 6 篇，1500 元/发布包'),
                    //'1' => '单篇发布',
                    //'2' => '打包发布',
                    ),
                ),
            ));
        /*$this->add(array(
            'name' => 'sel_right',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'sel_right',
                'class' => 'sel',
                'multiple' => 'multiple',
                'inarrayvalidator' => false,
                ),
            'options' => array(
                'registerInArrayValidator' => false,//doesn't work in zf2
                ),
            ));*/
        $this->add(array(
            'name' => 'sel_right',
            'attributes' => array(
                'type' => 'hidden',
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