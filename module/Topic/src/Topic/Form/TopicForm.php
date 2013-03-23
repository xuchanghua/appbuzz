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
                    '通讯.聊天'       => '通讯.聊天',
                    '网络.社区'       => '网络.社区',
                    '影音.图像'       => '影音.图像',
                    '办公.财经'       => '办公.财经',
                    '资讯.词典'       => '资讯.词典',
                    '旅行.地图'       => '旅行.地图',
                    '输入法.系统工具' => '输入法.系统工具',
                    '生活.购物'       => '生活.购物',
                    '美化.壁纸'       => '美化.壁纸',
                    '阅读.图书'       => '阅读.图书',
                    '其他'            => '其他',
                ),
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