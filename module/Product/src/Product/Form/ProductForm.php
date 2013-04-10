<?php
namespace Product\Form;

use Zend\Form\Form;

class ProductForm extends Form
{
    public function __construct($name = null)
    {
        // we want to ignore the name passed
        parent::__construct('product');
        $this->setAttribute('method', 'post');
        $this->add(array(
            'name' => 'id_product',
            'attributes' => array(
                'type'  => 'hidden',
            ),
        ));
        $this->add(array(
            'name' => 'name',
            'attributes' => array(
                'type'  => 'text',
                'placeholder' => '名称',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'description',
            'attributes' => array(
                'type' => 'textarea',
                'placeholder' => '描述',
                'rows' => 5,
            ),
        ));
        $this->add(array(
            'name' => 'type',
            'type' => 'Zend\Form\Element\Select',
            'options' => array(
                'value_options' => array(
                    '' => '请选择APP产品类型',
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
        $this->add(array(
            'name' => 'appstore_link',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'Appstore下载链接',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'androidmkt_link',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => '安卓市场下载链接',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'web_link',
            'attributes' => array(
                'type' => 'text',
                'placeholder' => 'web下载链接',
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