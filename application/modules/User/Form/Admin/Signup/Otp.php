<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 9747 2012-07-26 02:08:08Z john $
 * @author     Sami
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Admin_Signup_Otp extends Engine_Form {

  public function init() {
  
    $stepTable = Engine_Api::_()->getDbtable('signup', 'user');
    $stepRow = $stepTable->fetchRow($stepTable->select()->where('class = ?', 'User_Plugin_Signup_Otp'));
    
    $title = $this->getView()->translate('Step %d: Two Step Authentication', $stepRow->order);
    $this->setTitle($title)->setDisableTranslator(true);

    $this->addElement('Radio', 'enable', array(
      'label' => 'Enable Two Step Authentication?',
      'description' => 'If you have selected YES, members will receive a code on their registered mail id and have to enter that code for Signup. If you select NO, then they can directly Signup by filling the required form. Note: Place the tab after Create Account tab only.',
      'multiOptions' => array(
        '1' => 'Yes, allow to receive code for verification',
        '0' => 'No, do not enable two step authentication',
      ),
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));

    $this->populate(array(
      'enable' => $stepRow->enable,
    ));
  }
}
