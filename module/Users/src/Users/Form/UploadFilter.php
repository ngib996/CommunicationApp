<?php

namespace Users\Form;

use Zend\InputFilter\InputFilter;

class UploadFilter extends InputFilter {

  public function __construct() {
    $this->add(array(
        'name' => 'label',
        'required' => true,
        'filters' => array(
            array('name' => 'StripTags'),
        ),
        'validators' => array(
            array(
                'name' => 'StringLength',
                'options' => array(
                    'encoding' => 'UTF-8',
                    'min' => 2,
                    'max' => 140,
                ),
            ),
        ),
    ));
  }

}
