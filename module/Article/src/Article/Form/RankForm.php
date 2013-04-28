<?php
namespace Article\Form;

use Zend\Form\Form;

class RankForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('rank');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id_rank',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'obj_type',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_1',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#1',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_2',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#2',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_3',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#3',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_4',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#4',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_5',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#5',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_6',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#6',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_7',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#7',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_8',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#8',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_9',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#9',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_no_10',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '#10',
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