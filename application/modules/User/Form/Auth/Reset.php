<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Reset.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Auth_Reset extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Reset password?');

    // init password
    $this->addElement('Password', 'password', array(
      'label' => 'New Password',
      'required' => true,
      'allowEmpty' => false,
      'description' => 'Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.',
      'onkeyup' => 'passwordRoutine(this.value);',
      'validators' => array(
        array('NotEmpty', true),
        array('StringLength', false, array(6, 32)),
          array('Regex', true, array('/^(?=.*[A-Z].*)(?=.*[\!#\$%&\*\-\?\@\^])(?=.*[0-9].*)(?=.*[a-z].*).*$/')),

      ),
      'tabindex' => 1,
    ));


    $this->password->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    $this->password->getValidator('Regex')->setMessage('Password must be at least 6 characters and contain one upper and one lower case letter, one number and one special character.');
    $this->password->getValidator('NotEmpty')->setMessage('Please enter a valid password.', 'isEmpty');

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

    // init password_confirm
    $this->addElement('Password', 'password_confirm', array(
      'label' => 'Confirm New Password',
      'description' => 'Enter your password again for confirmation.',
      'required' => true,
      'allowEmpty' => false,
      'tabindex' => 2,
    ));
    $this->password_confirm->getDecorator('Description')->setOptions(array('placement' => 'APPEND'));
    
    $this->addElement('Checkbox', 'resetalldevice', array(
      'label' => 'Do you want to Logout from all the devices',
    ));
    // Init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Reset Password',
      'type' => 'submit',
      'ignore' => true,
      'tabindex' => 3,
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => Zend_Registry::get('Zend_Translate')->_(' or '),
      'href' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'default', true),
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array(
      'submit',
      'cancel'
    ), 'buttons', array(
      'decorators' => array(
        'FormElements',
        'DivDivDivWrapper',
      ),
    ));
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
