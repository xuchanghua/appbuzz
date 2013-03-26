<?php
namespace User\Form;

use Zend\Form\Form;

class UserForm extends Form
{
	public function __construct($name = null)
	{
		parent::__construct('user');
		$this->setAttribute('method', 'post');
		$this->add(array(
			'name' => 'id',
			'attributes' => array(
				'type' => 'hidden',
				),
			));
		$this->add(array(
			'name' => 'fk_user_type',
			'type' => 'Zend\Form\Element\Select',
			'options' => array(
				'value_options' => array(
					''  => '*用户类型',
					'1' => '企业用户',
					'2' => '媒体用户',
					'3' => '管理员用户',
					),
				),
			));
		/*
		$this->add(array(
			'name' => 'fk_user_type',
			'attributes' => array(
				'type' => 'hidden',
				),
			));
			*/
		$this->add(array(
			'name' => 'username',
			'type' => 'text',
			'attributes' => array(
				'placeholder' => '*用户名',
				),
			));
		$this->add(array(
			'name' => 'email',
			'attributes' => array(
				'type' => 'text',
				'placeholder' => '*电子邮箱',
				),
			));
		$this->add(array(
			'name' => 'password',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => '*密码',
				),
			));
		$this->add(array(
			'name' => 'confirmpassword',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => '*确认密码',
				),
			));
		$this->add(array(
			'name' => 'real_name',
			'attributes' => array(
				'type' => 'text',
				'placeholder' => '*真实姓名',
				),
			));
		$this->add(array(
			'name' => 'fk_enterprise',
			'attributes' => array(
				'type' => 'hidden'
				)
			));
		$this->add(array(
			'name' => 'fk_media',
			'attributes' => array(
				'type' => 'hidden'
				)
			));
		$this->add(array(
			'name' => 'password_salt',
			'attributes' => array(
				'type' => 'hidden'
				)
			));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Sign Up',
				'id' => 'submitbutton',
				'class' => 'btn btn-primary',
				),
			));

	}
}