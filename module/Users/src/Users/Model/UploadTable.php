<?php

namespace Users\Model;

use Zend\Db\Sql\Select;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

class uploadTable {

  protected $tableGateway;
  protected $uploadSharingTableGateway;

  public function __construct(TableGateway $tableGateway,TableGateway $uploadSharingTableGateway) {
    $this->tableGateway = $tableGateway;
    $this->uploadSharingTableGateway = $uploadSharingTableGateway;
  }

  public function fetchAll() {
    $resultSet = $this->tableGateway->select();
    return $resultSet;
  }

  public function saveUpload(Upload $upload) {
    $data = array('filename' => $upload->filename, 'label' => $upload->label, 'user_id' => $upload->user_id);
    $id = (int) $upload->id;
    if ($id == 0) {
      $this->tableGateway->insert($data);
    } else {
      if ($this->getUpload($id)) {
        $this->tableGateway->update($data, array('id' => $id));
      } else {
        throw new \Exception('Upload ID does not exist');
      }
    }
  }

  public function getUpload($id) {
    $id = (int) $id;
    $rowset = $this->tableGateway->select(array('id' => $id));
    $row = $rowset->current();
    if (!$row) {
      throw new \Exception("Could not find upload $id");
    }
    return $row;
  }
  
  public function getSharedUpload($id) {
  	$id = (int) $id;
  	$rowset = $this->uploadSharingTableGateway->select(array('id' => $id));
  	$row = $rowset->current();
  	if (!$row) {
  		throw new \Exception("Could not find upload sharing $id");
  	}
  	return $row;
  }

  public function getUploadsByUserId($userId) {

    $userId = (int) $userId;
    $rowset = $this->tableGateway->select(array('user_id' => $userId));
    return $rowset;
  }

  public function getUploadByUserIdAndFilename($userId,$filename) {
  
  	$userId = (int) $userId;
  	$rowset = $this->tableGateway->select(array('user_id' => $userId,'filename' => $filename));
  	$row = $rowset->current();
  	return $row;
  }
  public function deleteUpload($id) {
    $this->tableGateway->delete(array('id' => $id));
  }
  
  public function deleteSharedUpload($id) {
  	$this->uploadSharingTableGateway->delete(array('id' => $id));
  }

  public function addSharing($uploadId, $userId) {
    $data = array(
        'upload_id' => (int) $uploadId,
        'user_id' => (int) $userId,
    );
    //var_dump($data);
    $this->uploadSharingTableGateway->insert($data);
  }

  public function removeSharing($uploadId, $userId) {
    $data = array(
        'upload_id' => (int) $uploadId,
        'user_id' => (int) $userId,
    );
    $this->uploadSharingTableGateway->delete($data);
  }

  public function getSharedUsers($uploadId) {
    $uploadId = (int) $uploadId;
    $rowset = $this->uploadSharingTableGateway->select(array('upload_id' => $uploadId));
    return $rowset;
  }

  public function getSharedUploadsForUserId($userId) {
    $userId = (int) $userId;
    $rowset = $this->uploadSharingTableGateway->select(
            function (Select $select) use ($userId) {
      $select->columns(array())
              ->where(array('upload_sharing.user_id' => $userId))
              ->join('upload', 'upload_sharing.upload_id = upload.id');
    });
    return $rowset;
  }
}
  