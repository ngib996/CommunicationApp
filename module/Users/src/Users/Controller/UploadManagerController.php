<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Users\Model\Upload;
use Users\Form\UploadForm;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Authentication\AuthenticationService;

class UploadManagerController extends AbstractActionController {
	protected $storage;
	protected $authservice;
	public function getAuthService() {
		$this->authservice = $this->getServiceLocator ()->get ( 'AuthService' );
		return $this->authservice;
	}
	public function indexAction() {
		$uploadTable = $this->getServiceLocator ()->get ( 'UploadTable' );
		$userTable = $this->getServiceLocator ()->get ( 'UserTable' );
		// Get User Info from Session
		$userEmail = $this->getAuthService ()->getStorage ()->read ();
		if (! $userEmail) {
			return $this->redirect ()->toRoute ( 'users', array (
					'action' => 'index' 
			) );
		} else {
			$user = $userTable->getUserByEmail ( $userEmail );
			$sharedUploads = $uploadTable->getSharedUploadsForUserId ( $user->id );
			
			$sharedUploadsList = array ();
			foreach ( $sharedUploads as $sharedUpload ) {
				$sharedUsers=$uploadTable->getSharedUsers($sharedUpload->id);
				$sharedUserNames=array();
				foreach ( $sharedUsers as $sharedUser ) {
					$userI=$userTable->getUser($sharedUser->user_id);
					$userName=$userI->name;
					$sharedUserNames[]=$userName;
				}
				
				$uploadOwner = $userTable->getUser ( $sharedUpload->user_id );
				$sharedUploadInfo = array ();
				$sharedUploadInfo ['label'] = $sharedUpload->label;
				$sharedUploadInfo ['owner'] = $uploadOwner->name;
				
				$sharedUploadInfo ['sharedUsers'] = $sharedUserNames;
				$sharedUploadsList [$sharedUpload->id] = $sharedUploadInfo;
			}
			$view = new ViewModel ( array (
					'myUploads' => $uploadTable->getUploadsByUserId ( $user->id ),
					'sharedUploadsList' => $sharedUploadsList,
					'user' => $user
			) );
			return $view;
		}
	}
	public function getFileUploadLocation() {
		// Fetch Configuration from Module Config
		$config = $this->getServiceLocator ()->get ( 'config' );
		return $config ['module_config'] ['upload_location'];
	}
	public function addAction() {
		$form = new UploadForm ();
		$view = new ViewModel ( array (
				'form' => $form 
		) );
		return $view;
	}
	public function processUploadAction() {
		$userTable = $this->getServiceLocator ()->get ( 'UserTable' );
		$user_email = $this->getAuthService ()->getStorage ()->read ();
		$user = $userTable->getUserByEmail ( $user_email );
		$form = $this->getServiceLocator ()->get ( 'UploadForm' );
		$request = $this->getRequest ();
		if ($request->isPost ()) {
			$post = array_merge_recursive ( $request->getPost ()->toArray (), $request->getFiles ()->toArray () );
			$form->setData ( $post );
			$uploadFile = $this->params ()->fromFiles ( 'fileupload' );
			$upload = new Upload ();
			if ($form->isValid ()) {
				// Fetch Configuration from Module Config
				$uploadPath = $this->getFileUploadLocation ();
				// Save Uploaded file
				$adapter = new \Zend\File\Transfer\Adapter\Http ();
				$adapter->setDestination ( $uploadPath );
				if ($adapter->receive ( $uploadFile ['name'] )) {
					// File upload sucessfull
					$exchange_data = array ();
					$exchange_data ['label'] = $request->getPost ()->get ( 'label' );
					$exchange_data ['filename'] = $uploadFile ['name'];
					$exchange_data ['user_id'] = $user->id;
					$upload->exchangeArray ( $exchange_data );
					$uploadTable = $this->getServiceLocator ()->get ( 'UploadTable' );
					$uploadTable->saveUpload ( $upload );
					// add sharing info
					$upload = $uploadTable->getUploadByUserIdAndFilename ( $upload->user_id, $uploadFile ['name'] );
					$uploadTable->addSharing ( $upload->id, $user->id );
					return $this->redirect ()->toRoute ( 'users/upload-manager', array (
							'action' => 'index' 
					) );
				}
			}
		}
		return array (
				'form' => $form 
		);
	}
	public function deleteAction() {
		$uploadId = $this->params ()->fromRoute ( 'id' );
		$uploadTable = $this->getServiceLocator ()->get ( 'UploadTable' );
		$upload = $uploadTable->getUpload ( $uploadId );
		$uploadPath = $this->getFileUploadLocation ();
		// Remove File
		unlink ( $uploadPath . "/" . $upload->filename );
		// Delete Records
		$uploadTable->deleteUpload ( $uploadId );
		return $this->redirect ()->toRoute ( 'users/upload-manager' );
	}
	
	public function deleteShareAction() {
		$shareId = $this->params ()->fromRoute ( 'id' );
		$uploadTable = $this->getServiceLocator ()->get ( 'UploadTable' );
		$share = $uploadTable->getsharedUpload ( $shareId );
		$uploadId=$share->upload_id;
		// Delete Records
		$uploadTable->deleteSharedUpload ( $shareId );
		return $this->redirect ()->toRoute ( 'users/upload-manager', array('action' => 'edit','id' => $uploadId) );
	}
	
	public function processAddShareAction() {
		$uploadTable = $this->getServiceLocator()->get('UploadTable');
		$request = $this->getRequest ();
		if ($request->isPost()) {
		  $uploadId = $request->getPost()->get('upload_id');
		  $userId = $request->getPost()->get('user_id');
		  if ($userId) $uploadTable->addSharing($uploadId,$userId);
		  return $this->redirect ()->toRoute ( 'users/upload-manager', array('action' => 'edit','id' => $uploadId) );
		}
	}
	
	public function editAction() {
		// $this->layout('layout/myaccount');
		$uploadTable = $this->getServiceLocator ()->get ( 'UploadTable' );
		$userTable = $this->getServiceLocator ()->get ( 'UserTable' );
		//$loggedUserEmail = $this->getAuthService ()->getStorage ()->read ();
		//$loggedUser = $userTable->getUserByEmail ( $loggedUserEmail );
		
		$uploadId = $this->params ()->fromRoute ( 'id' );
		$upload = $uploadTable->getUpload ( $uploadId );
		
		//Shared Users List
		$SharedUsers=array();$SharedUsers2=array();
		$sharedUsersResult = $uploadTable->getSharedUsers($uploadId);
		foreach ($sharedUsersResult as $SharedUser) {
			$user=$userTable->getUser($SharedUser->user_id);
			$SharedUsers[$SharedUser->id] = $user->name;
			$SharedUsers2[$SharedUser->user_id] = $user->name;
				
		}

		// Add sharing
		$uploadShareForm = $this->getServiceLocator ()->get ( 'UploadShareForm' );
		$allusers = $userTable->fetchAll();
		$userList=array();
		foreach ($allusers as $user) {
			foreach ($SharedUsers2 as $id=>$name) {
				if ($id == $user->id) {continue 2; }
			}
			$userList[$user->id] = $user->name;
		}
		$uploadShareForm->get('upload_id')->setValue($uploadId);
		$uploadShareForm->get('user_id')->setValueOptions($userList);
		
		//edit form
		$form = $this->getServiceLocator ()->get ( 'UploadEditForm' );
		$form->bind ( $upload );
		$viewModel = new ViewModel ( array (
				'form' => $form,
				'upload_id' => $uploadId, 
				'sharedUsers' => $SharedUsers,
				'uploadShareForm' => $uploadShareForm
		) );
		return $viewModel;
	}
	public function processEditAction() {
		if (! $this->request->isPost ()) {
			return $this->redirect ()->toRoute ( 'users/upload-manager', array (
					'action' => 'edit' 
			) );
		}
		// Get User ID from POST
		$post = $this->request->getPost ();
		$uploadTable = $this->getServiceLocator ()->get ( 'UploadTable' );
		// Load User entity
		$upload = $uploadTable->getUpload ( $post->id );
		var_dump ( $upload );
		// Bind User entity to Form
		$form = $this->getServiceLocator ()->get ( 'UploadEditForm' );
		$form->bind ( $upload );
		$form->setData ( $post );
		if (! $form->isValid ()) {
			$model = new ViewModel ( array (
					'error' => true,
					'form' => $form 
			) );
			$model->setTemplate ( 'users/upload-manager/edit' );
			return $model;
		}
		// Save user and redirection
		$this->getServiceLocator ()->get ( 'UploadTable' )->saveUpload ( $upload );
		return $this->redirect ()->toRoute ( 'users/upload-manager' );
	}
	public function fileDownloadAction()
	{
		$uploadId = $this->params()->fromRoute('id');
		$uploadTable = $this->getServiceLocator()->get('UploadTable');
		$upload = $uploadTable->getUpload($uploadId);
	
		// Fetch Configuration from Module Config
		$uploadPath    = $this->getFileUploadLocation();
		$file = file_get_contents($uploadPath ."/" . $upload->filename);
	
		// Directly return the Response
		$response = $this->getEvent()->getResponse();
		$response->getHeaders()->addHeaders(array(
				'Content-Type' => 'application/octet-stream',
				'Content-Disposition' => 'attachment;filename="' .$upload->filename . '"',
		));
		$response->setContent($file);
	
		return $response;
	}
}
