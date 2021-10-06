<?php

class Serenity_Form_Admin_Settings_CustomTheme extends Engine_Form {

  public function init() {

    $this->setTitle('Add New Custom Theme')
        ->setMethod('post');

    $this->addElement('Text', 'name', array(
      'label' => 'Enter the new custom theme name.',
      'allowEmpty' => false,
      'required' => true,
    ));

    $customtheme_id = Zend_Controller_Front::getInstance()->getRequest()->getParam('customtheme_id', 0);
    
    if(empty($customtheme_id)) {
      $customThemes = Engine_Api::_()->getDbTable('customthemes', 'serenity')->getCustomThemes(array('all' => 1));
      if(count($customThemes) > 0) {
        foreach($customThemes as $customTheme){
          $themeOptions[$customTheme['theme_id']] = $customTheme['name'];
        }
        $this->addElement('Select', 'customthemeid', array(
            'label' => 'Choose From Existing Theme',
            'multiOptions' => $themeOptions,
            'escape' => false,
        ));
      }
    }

    $this->addElement('Button', 'submit', array(
      'label' => 'Create',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
        'label' => 'Cancel',
        'link' => true,
        'prependText' => ' or ',
        'href' => '',
        'onClick' => 'javascript:parent.Smoothbox.close();',
        'decorators' => array(
            'ViewHelper'
        )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
