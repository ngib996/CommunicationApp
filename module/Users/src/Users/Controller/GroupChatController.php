<?php

namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

use Users\Form\ChatForm;

class GroupChatController extends AbstractActionController {
	
	protected $storage;
	protected $authservice;
	
	protected function getAuthService()
	{
		if (! $this->authservice) {
			$this->authservice = $this->getServiceLocator()->get('AuthService');
		}
	
		return $this->authservice;
	}
	
	protected function getLoggedInUser()
	{
		$userTable = $this->getServiceLocator()->get('UserTable');
		$userEmail = $this->getAuthService()->getStorage()->read();
		$user = $userTable->getUserByEmail($userEmail);
	
		return $user;
	}
	
	protected function sendMessage($messageText, $fromUserId)
	{
		$chatMessageTG = $this->getServiceLocator()->get('ChatMessagesTableGateway');
		$data = array(
				'user_id' => $fromUserId,
				'message'  => $messageText,
		);
		$chatMessageTG->insert($data);
	
		return true;
	}
	
	public function indexAction() {
		$user = $this->getLoggedInUser();
		$request = $this->getRequest();
		if ($request->isPost()) {
			$messageText = $request->getPost()->get('message');
			$fromUserId = $user->id;
			$this->sendMessage($messageText, $fromUserId);
			// to prevent duplicate entries on refresh
			return $this->redirect()->toRoute('users/group-chat');
		}
		
		$form = new ChatForm();
		$viewModel  = new ViewModel(array('form' => $form,'userName' => $user->name));
		
		return $viewModel;
	}
	public function messageListAction() {
		$userTable = $this->getServiceLocator ()->get ( 'UserTable' );
		$chatMessageTG = $this->getServiceLocator ()->get ( 'ChatMessagesTableGateway' );
		$chatMessages = $chatMessageTG->select ();
		$messageList = array ();
		foreach ( $chatMessages as $chatMessage ) {
			$fromUser = $userTable->getUser ( $chatMessage->user_id );
			$messageData = array ();
			$messageData ['user'] = $fromUser->name;
			$messageData ['time'] = $chatMessage->stamp;
			$messageData ['data'] = $chatMessage->message;
			$messageList [] = $messageData;
		}
		$viewModel = new ViewModel ( array (
				'messageList' => $messageList 
		) );
		$viewModel->setTemplate ( 'users/group-chat/message-list' );
		$viewModel->setTerminal ( true );
		return $viewModel;
	}
}
