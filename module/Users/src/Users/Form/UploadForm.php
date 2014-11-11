<?php

// filename : module/Users/src/Users/Form/UploadForm.php

namespace Users\Form;

use Zend\Form\Form;
use Zend\Form\Element;

class UploadForm extends Form {

  public function __construct($name = null) {
    parent::__construct('Upload');
    $this->setAttribute('method', 'post');
    $this->setAttribute('enctype', 'multipart/formdata');
    $this->add(array(
        'name' => 'label',
        'attributes' => array('type' => 'text', 'required' => 'required'),
        'options' => array('label' => 'File Description')
    ));
    $this->add(array(
        'name' => 'fileupload',
        'attributes' => array('type' => 'file'),
        'options' => array('label' => 'File Upload')
    ));
    $this->add(array(// add the 5th field name=submit
        'name' => 'submit',
        'attributes' => array('type' => 'submit', 'value' => 'Upload')
    ));

    $file = new Element\File('fileupload');
    $file->setLabel('File Upload')->setAttribute('id', 'fileupload');
    $this->add($file);
  }

}
