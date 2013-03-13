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
        $this->add(array(
            'name' => 'id_newspub',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'body',
            'attributes' => array(
                'type'  => 'textarea',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'download_link',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'appstore_links',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'barcode',
            'attributes' => array(
                'type'  => 'text',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_pub_mode',
            'type'  => 'Zend\Form\Element\Select',
            'options' => array(
                'value_options' => array(
                    '1' => '单篇发布',
                    '2' => '打包发布',
                    ),
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