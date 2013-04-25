<?php
namespace User\Model;

use Zend\Db\TableGateway\TableGateway;  
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Zend\Paginator\Paginator;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Db\ResultSet\ResultSet;

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

	public function fetchAllDesc()
	{
		$resultSet = $this->tableGateway->select(function(Select $select){
			$select->order('id DESC');
		});
		return $resultSet;
	}

	public function fetchUserByFkType($fk_user_type)
	{
		$resultSet = $this->tableGateway->select(function(Select $select) use ($fk_user_type){
			$select->where->equalTo('fk_user_type', $fk_user_type);
		});
		return $resultSet;
	}

	//get the record of the `user` with the passed-in id
	public function getUser($id)
	{
		$rowset = $this->tableGateway->select(array('id' => $id));
		$row = $rowset->current();
		if(!$row){
			throw new \Exception("Could not find this $id");
		}
		return $row;
	}

	public function getUserByFkEnt($fk_enterprise)
	{
		$rowset = $this->tableGateway->select(array('fk_enterprise' => $fk_enterprise));
		$row = $rowset->current();
		if(!$row){
			throw new \Exception("Could not find this $fk_enterprise");
		}
		return $row;
	}

	public function getUserByFkMedia($fk_media)
	{
		$rowset = $this->tableGateway->select(array('fk_media' => $fk_media));
		$row = $rowset->current();
		if(!$row){
			throw new \Exception("Could not find this $fk_media");
		}
		return $row;
	}

	//get the record of the `user` with the passed-in username
	public function getUserByName($username)
	{
		$rowset = $this->tableGateway->select(array('username' => $username));
		$row = $rowset->current();
		if(!$row){
			throw new \Exception("Could not find this $username");
		}
		return $row;
	}

	public function saveUser(User $user)
	{
		$data = array(
			'username'      => $user->username,
			'password'      => $user->password, 
			'email'         => $user->email,
			'fk_user_type'  => $user->fk_user_type,
			'fk_enterprise' => $user->fk_enterprise,
			'fk_media'      => $user->fk_media,
			'real_name'     => $user->real_name,
			'created_at'    => $user->created_at,
			'created_by'    => $user->created_by,
			'updated_at'    => $user->updated_at,
			'is_writer'     => $user->is_writer,
			'updated_by'    => $user->updated_by,
		);
		$id = (int)$user->id;
		if($id == 0){
			$this->tableGateway->insert($data);
		} else {
			if($this->getUser($id)){
				$this->tableGateway->update($data, array('id' => $id));
			} else {
				throw new \Exception('Form id does not exist');
			}
		}
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

	public function deleteUser($id)
	{
		$this->tableGateway->delete(array('id' => $id));
	}
}