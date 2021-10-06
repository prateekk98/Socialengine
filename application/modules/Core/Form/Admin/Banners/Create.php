<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Banners_Create extends Engine_Form
{
  
  public function init()
  {
    // Set form attributes
    $this->setTitle('Create New Banner');
    $this->setDescription('Below you can create a new banner for your website. (Note: The recommended size for the image is: 1200px x 300px.)');
    $this->setAttrib('id', 'form-upload');
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    // Element: name
    $this->addElement('Text', 'title', array(
      'label' => 'Banner Heading',
      'allowEmpty' => false,
      'required' => true,
      'validators' => array(
        array('NotEmpty', true),
        array('StringLength', false, array(1, 64)),
      ),
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    $this->addElement('Textarea', 'body', array(
      'label' => 'Banner Sub Heading',
      'validators' => array(
        array('NotEmpty', true),
        array('StringLength', false, array(1, 256)),
      ),
      'filters' => array(
        'StripTags',
        new Engine_Filter_Censor(),
        new Engine_Filter_EnableLinks(),
      ),
    ));

    // Init file
    $this->addElement('File', 'photo', array(
      'label' => 'Banner Image'
    ));
    $this->photo->addValidator('Extension', false, 'jpg,png,gif,jpeg');

    $this->addElement('Text', 'label', array(
      'label' => 'CTA Button Label',
      'description' => 'Enter the label for this CTA button. This button will appear at the bottom right corner of your banner.',
    ));

    $this->addElement('Text', 'uri', array(
      'label' => 'CTA Button URL',
      'description' => 'Enter the URL of the page where you want to redirect users after they click on this button.',
    ));
    
    // Element: submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Create Banner',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));


    // Element: cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'banners', 'action' => 'index'), 'admin_default', true),
      'decorators' => array(
        'ViewHelper'
      )
    ));
    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
    $this->getDisplayGroup('buttons');
  }

}
