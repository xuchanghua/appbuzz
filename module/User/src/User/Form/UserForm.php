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
					''  => 'User Type',
					'1' => 'Enterprise User',
					'2' => 'Media User',
					'3' => 'Admin User',
					),
				),
			));
		$this->add(array(
			'name' => 'username',
			'type' => 'text',
			'attributes' => array(
				'placeholder' => 'Username',
				),
			));
		$this->add(array(
			'name' => 'email',
			'attributes' => array(
				'type' => 'text',
				'placeholder' => 'E-mail',
				),
			));
		$this->add(array(
			'name' => 'password',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => 'Password',
				),
			));
		$this->add(array(
			'name' => 'confirmpassword',
			'attributes' => array(
				'type' => 'password',
				'placeholder' => 'Confirm Password',
				),
			));
		$this->add(array(
			'name' => 'submit',
			'attributes' => array(
				'type' => 'submit',
				'value' => 'Sign Up',
				'id' => 'submitbutton',
				),
			));
	}
}