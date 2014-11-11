<?php

namespace Users\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class UserTable {
	protected $tableGateway;
	public function __construct(TableGateway $tableGateway) {
		$this->tableGateway = $tableGateway;
	}
	public function fetchAll() {
		$resultSet = $this->tableGateway->select ();
		return $resultSet;
	}
	public function saveUser(User $user) {
		$data = array (
				'email' => $user->email,
				'name' => $user->name,
				'password' => $user->password 
		);
		
		$id = ( int ) $user->id;
		if ($id == 0) {
			$this->tableGateway->insert ( $data );
		} else {
			if ($this->getUser ( $id )) {
				$this->tableGateway->update ( $data, array (
						'id' => $id 
				) );
			} else {
				throw new \Exception ( 'User ID does not exist' );
			}
		}
	}
	
	/**
	 * Get User account by UserId
	 *
	 * @param string $id        	
	 * @throws \Exception
	 * @return Row
	 */
	public function getUser($id) {
		$id = ( int ) $id;
		$rowset = $this->tableGateway->select ( array (
				'id' => $id 
		) );
		$row = $rowset->current();
		if (! $row) {
			throw new \Exception ( "Could not find row $id" );
		}
		return $row;
	}
	/**
	 * Get User account by UserEmail
	 *
	 * @param string $userEmail
	 * @throws \Exception
	 * @return Row
	 */
 public function getUserByEmail($userEmail) {
 	$rowset = $this->tableGateway->select(array('email' => $userEmail));
    $row = $rowset->current();
    if (!$row) {
      throw new \Exception("Could not find user $userEmail");
    }
    return $row;
  }
	public function deleteUser($id) {
		$this->tableGateway->delete ( array (
				'id' => $id 
		) );
	}
}
