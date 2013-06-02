<?php
namespace Topic\Form;

use Zend\Form\Form;

class TopicForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('topic');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id_topic',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'topic_type',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '选题类型',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'topic_link',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '选题链接',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'abstract',
            'attributes' => array(
                'type'  => 'textarea',
                'rows' => 5,
                'placeholder' => '选题概要',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'app_type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'value_options' => array(
                    '' => '请选择征集APP产品类型',
                    '图书'       => '图书',
                    '商业'       => '商业',
                    '商品指南'   => '商品指南',
                    '教育'       => '教育',
                    '娱乐'       => '娱乐',
                    '财务'       => '财务',
                    '美食佳饮'   => '美食佳饮',
                    '游戏'       => '游戏',
                    '健康健美'   => '健康健美',
                    '生活'       => '生活',
                    '医疗'       => '医疗',
                    '音乐'       => '音乐',
                    '导航'       => '导航',
                    '新闻'       => '新闻',
                    '报刊杂志'   => '报刊杂志',
                    '摄影与录像' => '摄影与录像',
                    '效率'       => '效率',
                    '参考'       => '参考',
                    '社交'       => '社交',
                    '体育'       => '体育',
                    '旅行'       => '旅行',
                    '工具'       => '工具',
                    '天气'       => '天气',
                ),
            ),
            'attributes' => array(                
                'multiple' => 'multiple',
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
        /*$this->add(array(
            'name' => 'app_type',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '征集APP产品类型',
            ),
            'options' => array(
                'label' => '',
            ),
        ));*/
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