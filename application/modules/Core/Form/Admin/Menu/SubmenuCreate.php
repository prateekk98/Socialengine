<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SubMenuCreate.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Menu_SubmenuCreate extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Create Submenu & Menu Item')
      ->setAttrib('class', 'global_form_popup')
      ;

    $this->addElement('Text', 'title', array(
      'label' => 'Submenu Title',
      'required' => true,
      'allowEmpty' => false,
    ));

    $this->addElement('Text', 'label', array(
        'label' => 'Label',
        'required' => true,
        'allowEmpty' => false,
      ));
  
      $this->addElement('Text', 'uri', array(
        'label' => 'URL (Note: If you do not want to give any URL for this, then enter javascript:void(0) below.)',
        'required' => true,
        'allowEmpty' => false,
        'style' => 'width: 300px',
        //'validators' => array(
        //  array('NotEmpty', true),
        //)
      ));
  
      $this->addElement('Text', 'icon', array(
        'label' => 'Icon / Icon Class (Note: Not all menus support icons.)',
        'style' => 'width: 500px',
      ));
  
      $this->addElement('Checkbox', 'target', array(
        'label' => 'Open in a new window?',
        'checkedValue' => '_blank',
        'uncheckedValue' => '',
      ));
  
      $this->addElement('Checkbox', 'enabled', array(
        'label' => 'Enabled?',
        'checkedValue' => '1',
        'uncheckedValue' => '0',
        'value' => '1',
      ));

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Create Submenu',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      )
    ));
    
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
