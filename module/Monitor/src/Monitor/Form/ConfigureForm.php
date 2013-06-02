<?php
namespace Monitor\Form;

use Zend\Form\Form;

class ConfigureForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('configure');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'myapp',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'myapp2',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'myapp3',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'competitorapp',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'competitorapp2',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'competitorapp3',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'competitorapp4',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'competitorapp5',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '',
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