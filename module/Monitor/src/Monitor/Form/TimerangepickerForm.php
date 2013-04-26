<?php
namespace Monitor\Form;

use Zend\Form\Form;

class TimerangepickerForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('configure');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'start_date',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'YYYY-MM-DD',
                'id' => 'start_date',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'end_date',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'YYYY-MM-DD',
                'id' => 'end_date',
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