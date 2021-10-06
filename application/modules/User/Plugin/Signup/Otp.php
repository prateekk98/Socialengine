<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 10099 2013-10-19 14:58:40Z ivan $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class User_Plugin_Signup_Otp extends Core_Plugin_FormSequence_Abstract {

 	protected $_formClass = 'User_Form_Signup_Otp';
 	protected $_script = array('signup/form/otp.tpl', 'user');
 	protected $_adminFormClass = 'User_Form_Admin_Signup_Otp';
 	protected $_adminScript = array('admin-signup/otp.tpl', 'user');
 	protected $_skip;

 	public function onSubmit(Zend_Controller_Request_Abstract $request) {
    if(!$this->getForm()->isValid($request->getPost())) {
      $this->getSession()->active = true;
      $this->onSubmitNotIsValid();
      return false;
    }
    $inputcode = $request->getParam("code");
    $email = $_SESSION['User_Plugin_Signup_Account']['data']['email'];
    $code_id = Engine_Api::_()->getDbtable('codes', 'user')->isExist($inputcode, $email);
    if(empty($code_id)) {
      $this->getForm()->addError("The verification code you entered is invalid. Please enter the correct verification code.");
      return;
    } else {
      $code = Engine_Api::_()->getItem('user_code', $code_id);
      $code->delete();
    }
    parent::onSubmit($request);
	}

  public function onAdminProcess($form) {

    $stepTable = Engine_Api::_()->getDbtable('signup', 'user');
    $stepRow = $stepTable->fetchRow($stepTable->select()->where('class = ?', 'User_Plugin_Signup_Otp'));
    $stepRow->enable = $form->getValue('enable');
    $stepRow->save();

    $settings = Engine_Api::_()->getApi('settings', 'core');
    $values = $form->getValues();
    $form->addNotice('Your changes have been saved.');
  }
}
