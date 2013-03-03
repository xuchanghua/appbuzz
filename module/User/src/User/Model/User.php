<?php
namespace User\Model;

class User
{
	public $id;
	public $username;
	public $password;
	public $password_salt;
	public $real_name;
	public $fk_user_type;

	public function exchangeArray($data)
	{
		$this->id            = (isset($data['id']))            ? $data['id']            : null;
		$this->username      = (isset($data['username']))      ? $data['username']      : null;
		$this->password      = (isset($data['password']))      ? $data['password']      : null;
		$this->password_salt = (isset($data['password_salt'])) ? $data['password_salt'] : null;
		$this->real_name     = (isset($data['real_name']))     ? $data['real_name']     : null;
		$this->fk_user_type  = (isset($data['fk_user_type']))  ? $data['fk_user_type']  : null;
	}
}