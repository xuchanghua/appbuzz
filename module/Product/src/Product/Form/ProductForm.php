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
        $this->setAttribute('enctype', 'multipart/form-data');
        
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
                    ''           => '请选择APP产品类型',
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
            'name' => 'MAX_FILE_SIZE',
            'attributes' => array(
                'type' => 'hidden',
                'value' => '100000000'
            ),
        ));
        $this->add(array(
            'name' => 'barcode',
            'attributes' => array(
                'type'  => 'file',
                'placeholder' => '下载二维码',
            ),
            'options' => array(
                'label' => '',
            ),
        ));
        $this->add(array(
            'name' => 'fk_appicon',
            'attributes' => array(
                'type'  => 'file',
                'placeholder' => '产品图标',
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