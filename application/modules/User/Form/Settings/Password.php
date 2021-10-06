<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Password.php 9747 2012-07-26 02:08:08Z john $
 * @author     Steve
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Settings_Password extends Engine_Form
{
  public function init()
  {
    // @todo fix form CSS/decorators
    // @todo replace fake values with real values
    $this->setTitle('Change Password')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ;

    // Init old password
    $this->addElement('Password', 'oldPassword', array(
      'label' => 'Old Password',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Init password
    $this->addElement('Password', 'password', array(
      'label' => 'New Password',
        'description' => 'Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.',
        'required' => true,
      'onkeyup'=>'return passwordRoutine(this.value);',
      'allowEmpty' => false,
      'validators' => array(
        array('stringLength', false, array(6, 32)),
          array('Regex', true, array('/^(?=.*[A-Z].*)(?=.*[\!#\$%&\*\-\?\@\^])(?=.*[0-9].*)(?=.*[a-z].*).*$/')),
      )));
      $this->password->getDecorator('Description')->setOption('placement', 'APPEND');
      $this->password->getValidator('Regex')->setMessage('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.');

      $regexCheck = new Engine_Validate_Callback(array($this, 'regexCheck'), $this->password);
      $regexCheck->setMessage("Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.");
      $this->password->addValidator($regexCheck);
      $translate = Zend_Registry::get('Zend_Translate');

      $this->addElement('Dummy', 'passwordroutine', array(
          'label' => '',
          'content' => '
          <div id="pswd_info">
            <ul>
              <li id="passwordroutine_length" class="invalid"><span>'.$translate->translate("6 characters").'</span></li>
                <li id="passwordroutine_capital" class="invalid"><span>'.$translate->translate("1 uppercase").'</span></li>
                <li id="passwordroutine_lowerLetter" class="invalid"><span>'.$translate->translate("1 lowercase").'</span></li>
                <li id="passwordroutine_number" class="invalid"><span>'.$translate->translate("1 number").'</span></li>
                <li id="passwordroutine_specialcharacters" class="invalid"><span>'.$translate->translate("1 special").'</span><span class="special_char_ques"> <i class="far fa-question-circle"></i><div class="special_char_overlay">'.$translate->translate("Special Characters Allowed !#$%&*-?@^").'</div></span></li>
            </ul>
          </div>',
      ));

    // Init password confirm
    $this->addElement('Password', 'passwordConfirm', array(
      'label' => 'New Password (again)',
      'description' => 'Enter your password again for confirmation.',
      'required' => true,
      'allowEmpty' => false
    ));
    $this->passwordConfirm->getDecorator('Description')->setOption('placement', 'APPEND');

    $this->addElement('Hidden','require_password',array('order'=>999,'value'=>0));

    $this->addElement('Checkbox', 'resetalldevice', array(
      'label' => 'Do you want to Logout from all the devices',
    ));

    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Change Password',
      'type' => 'submit',
      'ignore' => true
    ));

    // Create display group for buttons
    #$this->addDisplayGroup($emailAlerts, 'checkboxes');

    // Set default action
    $this->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
  }
  public function regexCheck($value)
  {
    if(preg_match("/([\\\\:\/])/", $value))
    {
       return false;
    }

    return true;
  }
}
