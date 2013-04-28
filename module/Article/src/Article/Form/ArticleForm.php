<?php
namespace Article\Form;

use Zend\Form\Form;

class ArticleForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('article');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id_article',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'label',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '标签',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'title',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '文章标题',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'body',
            'attributes' => array(
                'type' => 'textarea',
                'placeholder' => '正文',
                'rows' => 7,
            ),
        ));        
        $this->add(array(
            'name' => 'abstract',
            'attributes' => array(
                'type'  => 'textarea',
                'placeholder' => '摘要',
                'rows' => 3,
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'author',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '作者',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'pub_date',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '发表日期',
                'id' => 'f_date_ETA',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'rank',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '排序',
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