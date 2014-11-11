<?php

// filename : module/Users/src/Users/Form/UploadForm.php
namespace Users\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class UploadEditForm extends Form {
	public function __construct($name = null) {
		parent::__construct ( 'Edit Upload' );
		$this->setAttribute ( 'method', 'post' );
		$this->setAttribute ( 'enctype', 'multipart/form-data' );
		$this->add ( array (
				'name' => 'id',
				'attributes' => array (
						'type' => 'hidden',
				)
		) );
		$this->add ( array (
				'name' => 'user_id',
				'attributes' => array (
						'type' => 'hidden',
				)
		) );
		$this->add ( array (
				'name' => 'label',
				'attributes' => array (
						'type' => 'text',
						'required' => 'required' 
				),
				'options' => array (
						'label' => 'File Description' 
				) 
		) );
		$this->add(array(
				'name' => 'filename',
				'attributes' => array('type' => 'text','required' => 'required','readonly' => "true"),
				'options' => array('label' => 'File Name')
		));
		$this->add ( array ( // add the 5th field name=submit
				'name' => 'submit',
				'attributes' => array (
						'type' => 'submit',
						'value' => 'Save' 
				) 
		) );
	}
}
