<?php

class Core_Form_Admin_Widget_LandingPageFeatures extends Engine_Form {

  public function init() {
  
    // Get available files
    $banner_options = array('' => '');
    $files = Engine_Api::_()->getDbTable('files', 'core')->getFiles(array('fetchAll' => 1, 'extension' => array('gif', 'jpg', 'jpeg', 'png')));
    foreach( $files as $file ) {
      $banner_options[$file->storage_path] = $file->name;
    }

    //Feature 1
    $this->addElement('Dummy', "dummy1", array(
        'label' => "<span style='font-weight:bold;'>Feature 1</span>",
    ));
    $this->getElement('dummy1')->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    $this->addElement('Select', "fe1img", array(
        'label' => 'Upload Icon',
        'multiOptions' => $banner_options,
    ));
    $this->addElement('Text', "fe1heading", array(
      'label' => 'Title',
    ));
    $this->addElement('Text', "fe1description", array(
      'label' => 'Description',
    ));
    
    //Feature 2
    $this->addElement('Dummy', "dummy2", array(
        'label' => "<span style='font-weight:bold;'>Feature 2</span>",
    ));
    $this->getElement('dummy2')->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    $this->addElement('Select', "fe2img", array(
        'label' => 'Upload Icon',
        'multiOptions' => $banner_options,
    ));
    $this->addElement('Text', "fe2heading", array(
      'label' => 'Title',
    ));
    $this->addElement('Text', "fe2description", array(
      'label' => 'Description',
    ));
    
    //Feature 3
    $this->addElement('Dummy', "dummy3", array(
        'label' => "<span style='font-weight:bold;'>Feature 3</span>",
    ));
    $this->getElement('dummy3')->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    $this->addElement('Select', "fe3img", array(
        'label' => 'Upload Icon',
        'multiOptions' => $banner_options,
    ));
    $this->addElement('Text', "fe3heading", array(
      'label' => 'Title',
    ));
    $this->addElement('Text', "fe3description", array(
      'label' => 'Description',
    ));
    
    //Feature 4
    $this->addElement('Dummy', "dummy4", array(
        'label' => "<span style='font-weight:bold;'>Feature 4</span>",
    ));
    $this->getElement('dummy4')->getDecorator('Label')->setOptions(array('placement' => 'PREPEND', 'escape' => false));
    $this->addElement('Select', "fe4img", array(
        'label' => 'Upload Icon',
        'multiOptions' => $banner_options,
    ));
    $this->addElement('Text', "fe4heading", array(
      'label' => 'Title',
    ));
    $this->addElement('Text', "fe4description", array(
      'label' => 'Description',
    ));
  }
}
