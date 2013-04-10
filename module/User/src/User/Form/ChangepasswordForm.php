<?php
namespace User\Form;

use Zend\Form\Form;

class ChangepasswordForm extends Form
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
			'name' => 'originalpassword',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => '*原密码',
				),
			));
		$this->add(array(
			'name' => 'password',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => '*新密码',
				),
			));
		$this->add(array(
			'name' => 'confirmpassword',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => '*确认新密码',
				),
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