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

	//SELECT * FROM `user`
	public function fetchAll()
	{
		$resultSet = $this->tableGateway->select();
		return $resultSet;
	}

	//get the record of the `user` with the passed-in username
	public function getUser($username)
	{
		$rowset = $this->tableGateway->select(array('username' => $username));
		$row = $rowset->current();
		if(!$row){
			throw new \Exception("Could not find this $username");
		}
		return $row;
	}

	//check if the username passed in was exist in the `user` table
	public function checkUser($username)
	{
		$rowset = $this->tableGateway->select(array('username' => $username));
		$row = $rowset->current();
		if(!$row){
			return false;

		}
		else
		{
			return true;
		}
	}
}