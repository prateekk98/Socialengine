<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: Delete.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */
class Authorization_Form_Admin_ProfileTypeLevelMap_Delete extends Engine_Form
{
  public function init()
  {
    $this->setTitle("Delete Profile Type to Member Level mapping")
      ->setDescription('Are you sure you want to delete this mapping ? You can move all the users of this '
        . 'Profile Type from their existing Member Level to a different one by selecting it below.')
      ->setAttrib('class', 'global_form_popup');

    // Element: level_id
    $this->addElement('Select', 'level_id', array(
      'label' => 'Member Level',
      'multiOptions' => Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc(),
      'ignore' => true,
      'value' => Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel()->level_id,
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Delete Mapping',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'prependText' => ' or ',
      'ignore' => true,
      'link' => true,
      'onClick' => 'parent.Smoothbox.close();',
      'decorators' => array('ViewHelper'),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
