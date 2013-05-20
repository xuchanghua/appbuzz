<?php
namespace Interview\Form;

use Zend\Form\Form;

class InterviewForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('interview');
        $this->setAttribute('method', 'post');        
        $this->setAttribute('enctype', 'multipart/form-data');
        $this->add(array(
            'name' => 'id_interview',
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
            'name' => 'fk_enterprise_user',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'fk_media_user',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'date_time',
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
            'name' => 'purpose',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '采访目的',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'outline',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '采访纲要',
                'rows'        => 5,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_interview_status',
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
            'name' => 'screen_shot',
            'type' => 'file',
            'attributes' => array(
                'id' => 'screen_shot',
                'multiple' => true
            ),
        ));
        for($i = 1; $i < 11; $i++)
        {
            $this->add(array(
                'name' => 'q'.$i,
                'attributes' => array(
                    'type' => 'text',
                ),
            ));
            $this->add(array(
                'name' => 'a'.$i,
                'attributes' => array(
                    'type' => 'text',
                ),
            ));
        }
        $this->add(array(
            'name' => 'submit',
            'attributes' => array(
                'type'  => 'submit',
                'value' => 'Go',
                'id' => 'submitbutton',
                'class' => 'btn btn-primary'
            ),
        ));
    }
}