<?php
namespace User\Model;

use Zend\InputFilter\Factory as InputFactory;
use Zend\InputFilter\InputFilter;
use Zend\InputFilter\InputFilterAwareInterface;
use Zend\InputFilter\InputFilterInterface;

class User implements InputFilterAwareInterface
{
	public $id;
	public $username;
	public $password;
	public $password_salt;
	public $real_name;
	public $fk_user_type;
	protected $inputFilter;

	public function exchangeArray($data)
	{
		$this->id            = (isset($data['id']))            ? $data['id']            : null;
		$this->username      = (isset($data['username']))      ? $data['username']      : null;
		$this->password      = (isset($data['password']))      ? $data['password']      : null;
		$this->password_salt = (isset($data['password_salt'])) ? $data['password_salt'] : null;
		$this->real_name     = (isset($data['real_name']))     ? $data['real_name']     : null;
		$this->fk_user_type  = (isset($data['fk_user_type']))  ? $data['fk_user_type']  : null;
		$this->email         = (isset($data['email']))         ? $data['email']         : null;
	}

	public function setInputFilter(InputFilterInterface $inputFilter)
	{
		throw new \Exception("Not used");
	}

	public function getInputFilter()
	{
		if(!$this->inputFilter){
			$inputFilter = new InputFilter();
			$factory     = new InputFactory();

			$inputFilter->add($factory->createInput(array(
				'name'     => 'id',
				'required' => true,
				'filters'  => array(
					array('name' => 'Int'),
					),
				)));

			$inputFilter->add($factory->createInput(array(
				'name'     => 'username',
				'required' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
					),
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 50,
							),
						),
					),
				)));

			$inputFilter->add($factory->createInput(array(
				'name'     => 'email',
				'required' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
					),
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 255,
							),
						),
					),
				)));

			$inputFilter->add($factory->createInput(array(
				'name'     => 'password',
				'required' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
					),
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 32,
							),
						),
					),
				)));

			$inputFilter->add($factory->createInput(array(
				'name'     => 'confirmpassword',
				'required' => true,
				'filters'  => array(
					array('name' => 'StripTags'),
					array('name' => 'StringTrim'),
					),
				'validators' => array(
					array(
						'name' => 'StringLength',
						'options' => array(
							'encoding' => 'UTF-8',
							'min'      => 1,
							'max'      => 32,
							),
						),
					),
				)));

			$this->inputFilter = $inputFilter;
		}

		return $this->inputFilter;
	}
}