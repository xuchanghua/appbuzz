<?php
namespace Newspub\Form;

use Zend\Form\Form;

class NpmediaForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('npmedia');
        $this->setAttribute('method', 'post');
        $this->setAttribute('enctype', 'multipart/form-data');

        $this->add(array(
            'name' => 'id_npmedia',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'fk_media_user',
            'attributes' => array(
                'type' => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'score',
            'type' => 'Zend\Form\Element\Select',
            'attributes' => array(
                'id' => 'score',
            ),
            'options' => array(
                'value_options' => array(
                    '1分' => '1分',
                    '2分' => '2分',
                    '3分' => '3分',
                    '4分' => '4分',
                    '5分' => '5分',
                ),
            ),
        ));
        $this->add(array(
            'name' => 'news_link',
            'required' => true,
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '新闻链接',
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