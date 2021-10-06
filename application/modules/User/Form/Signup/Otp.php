<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Form_Signup_Otp extends Engine_Form {

  public function init() {
    
    $translate = Zend_Registry::get('Zend_Translate');
    
    $this->setTitle('Two Step Authentication');
    
    $this->addElement('Dummy', 'description', array(
      'content' => "<div class='twostep_verify_message'><p class='_head'><i class='fas fa-check-circle'></i><b>".$translate->translate("A verification code has been sent to your email account.")."</b></p><p class='_des'>".$translate->translate("Please copy the verification code that has just been sent to your email account and paste in the field below to verify your email and continue the registration process.")."</p></div>",
    ));

    $this->setAttrib('enctype', 'multipart/form-data')
        ->setAttrib('id', 'SignupForm')
        ->setAttrib('class', 'twostep_auth_form');

    $this->addElement('Text', "code", array(
        'label' => 'Enter Verification Code',
        'description' => '',
        'allowEmpty' => false,
        'required' => true,
    ));

    $this->addElement('Hash', 'token');

    $this->addElement('Hidden', 'nextStep', array(
      'order' => 3
    ));

    // Element: done
    $this->addElement('Button', 'done', array(
      'label' => 'Save',
      'type' => 'submit',
      'onclick' => 'javascript:finishForm();',
      'decorators' => array(
      'ViewHelper',
      ),
    ));
  }
}
