<?php

namespace Users;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;

use Users\Model\User;
use Users\Model\UserTable;
use Users\Model\Upload;
use Users\Model\UploadTable;

use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use Zend\Authentication\Adapter\DbTable as DbTableAuthAdapter;
use Zend\Authentication\AuthenticationService;

class Module implements AutoloaderProviderInterface {
	public function getAutoloaderConfig() {
		return array (
				'Zend\Loader\ClassMapAutoloader' => array (
						__DIR__ . '/autoload_classmap.php' 
				),
				'Zend\Loader\StandardAutoloader' => array (
						'namespaces' => array (
								// if we're in a namespace deeper than one level we need to fix the \ in the path
								__NAMESPACE__ => __DIR__ . '/src/' . str_replace ( '\\', '/', __NAMESPACE__ ) 
						) 
				) 
		);
	}
	public function getConfig() {
		return include __DIR__ . '/config/module.config.php';
	}
	public function onBootstrap(MvcEvent $e) {
		// You may not need to do this if you're doing it elsewhere in your
		// application
		$eventManager = $e->getApplication ()->getEventManager ();
		$moduleRouteListener = new ModuleRouteListener ();
		$moduleRouteListener->attach ( $eventManager );
	}
	public function getServiceConfig() {
		return array (
				'abstract_factories' => array (),
				'aliases' => array (),
				'factories' => array (
						// SERVICES
						'AuthService' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$dbTableAuthAdapter = new DbTableAuthAdapter ( $dbAdapter, 'user', 'email', 'password', 'MD5(?)' );
							
							$authService = new AuthenticationService ();
							$authService->setAdapter ( $dbTableAuthAdapter );
							return $authService;
						},
						
						// DB
						'UserTable' => function ($sm) {
							$tableGateway = $sm->get ( 'UserTableGateway' );
							$table = new UserTable ( $tableGateway );
							return $table;
						},
						'UserTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new User () );
							return new TableGateway ( 'user', $dbAdapter, null, $resultSetPrototype );
						},
						
						'UploadTable' => function ($sm) {						// model class name
							$tableGateway = $sm->get ( 'UploadTableGateway' );	// $1 du constructor
							$uploadSharingTableGateway = $sm->get ( 'UploadSharingTableGateway' ); //$2 du constructor
							$table = new UploadTable ( $tableGateway,$uploadSharingTableGateway );
							return $table;
						},
						'UploadTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							$resultSetPrototype = new ResultSet ();
							$resultSetPrototype->setArrayObjectPrototype ( new Upload () );
							return new TableGateway ( 'upload', $dbAdapter, null, $resultSetPrototype );
						},
						
						'UploadSharingTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							return new TableGateway ( 'upload_sharing', $dbAdapter );
						},
						'ChatMessagesTableGateway' => function ($sm) {
							$dbAdapter = $sm->get ( 'Zend\Db\Adapter\Adapter' );
							//$resultSetPrototype = new ResultSet ();
							//$resultSetPrototype->setArrayObjectPrototype ( new User () );
							//return new TableGateway ( 'chat_messages', $dbAdapter, null, $resultSetPrototype );
							return new TableGateway ( 'chat_messages', $dbAdapter );
						},
						
						// FORMS
						'LoginForm' => function ($sm) {
							$form = new \Users\Form\LoginForm ();
							$form->setInputFilter ( $sm->get ( 'LoginFilter' ) );
							return $form;
						},
						'RegisterForm' => function ($sm) {
							$form = new \Users\Form\RegisterForm ();
							$form->setInputFilter ( $sm->get ( 'RegisterFilter' ) );
							return $form;
						},
						'UserEditForm' => function ($sm) {
							$form = new \Users\Form\UserEditForm ();
							$form->setInputFilter ( $sm->get ( 'UserEditFilter' ) );
							return $form;
						},
						'UploadForm' => function ($sm) {
							$form = new \Users\Form\UploadForm ();
							return $form;
						},
						'UploadEditForm' => function ($sm) {
							$form = new \Users\Form\UploadEditForm ();
							return $form;
						},
						'UploadShareForm' => function ($sm) {
							$form = new \Users\Form\UploadShareForm ();
							return $form;
						},
						
						// FILTERS
						'LoginFilter' => function ($sm) {
							return new \Users\Form\LoginFilter ();
						},
						'RegisterFilter' => function ($sm) {
							return new \Users\Form\RegisterFilter ();
						},
						'UserEditFilter' => function ($sm) {
							return new \Users\Form\UserEditFilter ();
						} 
				)
				,
				'invokables' => array (),
				'services' => array (),
				'shared' => array () 
		);
	}
}
