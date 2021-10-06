<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Update.php 10249 2014-05-30 22:38:38Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Form_Admin_Settings_Emoticon_Update extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Add emoticon')
      ->setAttrib('enctype', 'multipart/form-data')
      ->setAttrib('id', 'emoticon_update_form')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));

    $this->addElement('Text', 'name', array(
      'label' => 'Emoticon Name',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '25'))
      )
    ));

    $this->addElement('Text', 'symbol', array(
      'label' => 'Emoticon Symbol',
      'required' => true,
      'allowEmpty' => false,
      'filters' => array(
        new Engine_Filter_Censor(),
        'StripTags',
        new Engine_Filter_StringLength(array('max' => '25'))
      )
    ));

    $this->addElement('File', 'Filedata', array(
      'label' => 'Choose a emoticon',
      'destination' => APPLICATION_PATH . '/application/modules/Activity/externals/emoticons/images',
      'validators' => array(
        array('Extension', false, 'jpg,jpeg,png'),
      ),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
