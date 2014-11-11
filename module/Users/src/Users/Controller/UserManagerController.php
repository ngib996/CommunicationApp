<?php
namespace Users\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;

use Users\Form\RegisterForm;
use Users\Form\RegisterFilter;

use Users\Model\User;
use Users\Model\UserTable;

class UserManagerController extends AbstractActionController
{
    public function indexAction()
    {
    	//$this->layout('layout/myaccount');
    	    	 
		$userTable = $this->getServiceLocator()->get('UserTable');
		$viewModel  = new ViewModel(array('users' => $userTable->fetchAll())); 
		return $viewModel; 
    }

    public function editAction()
    {
    	//$this->layout('layout/myaccount');
    	
    	$userTable = $this->getServiceLocator()->get('UserTable');

    	$user = $userTable->getUser($this->params()->fromRoute('id')); 
		$form = $this->getServiceLocator()->get('UserEditForm');
		$form->bind($user);
    	$viewModel  = new ViewModel(array('form' => $form, 'user_id' => $this->params()->fromRoute('id')));
    	return $viewModel;    	 
    }
    
    public function processAction()
    {
    	//$this->layout('layout/myaccount');
    	 
        if (!$this->request->isPost()) {
            return $this->redirect()->toRoute('users/user-manager', array('action' => 'edit'));
        }

        $post = $this->request->getPost();
        $userTable = $this->getServiceLocator()->get('UserTable');       
        $user = $userTable->getUser($post->id);
        
		$form = $this->getServiceLocator()->get('UserEditForm');
		$form->bind($user);	
        $form->setData($post);
        
        if (!$form->isValid()) {
            $model = new ViewModel(array(
                'error' => true,
                'form'  => $form,
            ));
            $model->setTemplate('users/user-manager/edit');
            return $model;
        }
		
        // Save user
        $this->getServiceLocator()->get('UserTable')->saveUser($user);
        
        return $this->redirect()->toRoute('users/user-manager');
    }
    
    public function deleteAction()
    {
    	//$this->layout('layout/myaccount');
    	$this->getServiceLocator()->get('UserTable')
				    				->deleteUser($this->params()
				    				->fromRoute('id'));
    	return $this->redirect()
    						->toRoute('users/user-manager');
    	 
    }
    
    public function addAction()
    {
    	//$this->layout('layout/myaccount');
    	$form = $this->getServiceLocator()->get('RegisterForm');
    	$viewModel  = new ViewModel(array('form' => $form));
    	return $viewModel;
    }
    public function processAddAction() {
    	if (!$this->request->isPost()) {
    		return $this->redirect()->toRoute('users/user-manager', array('action' => 'index'));
    	}
    	$post = $this->request->getPost();
    	$form = $this->getServiceLocator()->get('RegisterForm');
    	$form->setData($post);
    	if (!$form->isValid()) {
    		$model = new ViewModel(array('error' => true, 'form' => $form));
    		$model->setTemplate('users/user-manager/index');
    		return $model;
    	}
    	// Create user
    	$this->createUser($form->getData());
    	return $this->redirect()->toRoute('users/user-manager', array('action' => 'index'));
    }
    
    protected function createUser(array $data) {
    	$sm = $this->getServiceLocator();
    	$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
    	$resultSetPrototype = new \Zend\Db\ResultSet\ResultSet();
    	$resultSetPrototype->setArrayObjectPrototype(new \Users\Model\user);
    	$tableGateway = new \Zend\Db\TableGateway\TableGateway('user', $dbAdapter, null, $resultSetPrototype);
    	$user = new user();
    	$user->exchangeArray($data);
    	$userTable = $this->getServiceLocator()->get('UserTable');
    	$userTable->saveUser($user);
    	return true;
    }
    

}
