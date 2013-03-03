<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;

class UserTable
{
	protected $tableGateway;

	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}

	public function fetchAll()
	{
		$resultSet = $this->tableGateway->select();
		return $resultSet;
	}

	public function getUser($username)
	{
		$rowset = $this->tableGateway->select(array('username' => $username));
		$row = $rowset->current();
		if(!$row){
			throw new \Exception("Could not find this $username");
		}
		return $row->password;
	}

	public function checkUser($username, $password)
	{

	}

}