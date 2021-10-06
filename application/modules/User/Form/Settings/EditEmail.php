<?php

/**
 * SocialEngineSolutions
 *
 * @category   Application_Sespage
 * @package    Sespage
 * @copyright  Copyright 2017-2018 SocialEngineSolutions
 * @license    http://www.socialenginesolutions.com/license/
 * @version    $Id: Delete.php  2018-04-23 00:00:00 SocialEngineSolutions $
 * @author     SocialEngineSolutions
 */
class User_Form_Settings_EditEmail extends Engine_Form {

  protected $_item;

  public function setItem(User_Model_User $item)
  {
    $this->_item = $item;
  }

  public function getItem()
  {
    if( null === $this->_item ) {
      throw new User_Model_Exception('No item set in ' . get_class($this));
    }

    return $this->_item;
  }
  
  public function init() {
  
    $this->setTitle('Edit Email')
      ->setDescription('Enter your new email below.')
      ->setAttrib('class', 'global_form_popup edit_email_popup')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()))
      ->setMethod('POST');
    
    // Init email
    $this->addElement('Text', 'email', array(
      'label' => 'Email Address',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('NotEmpty', true),
        array('EmailAddress', true),
        array('Db_NoRecordExists', true, array(Engine_Db_Table::getTablePrefix().'users', 'email', array('field' => 'user_id', 'value' => $this->getItem()->getIdentity())))
      ),
      'filters' => array(
        'StringTrim'
      )
    ));
    $this->email->getValidator('NotEmpty')->setMessage('Please enter a valid email address.', 'isEmpty');
    $this->email->getValidator('Db_NoRecordExists')->setMessage('Someone has already registered this email address, please use another one.', 'recordFound');
    $this->email->getValidator('EmailAddress')->getHostnameValidator()->setValidateTld(false);

    
    $this->addElement('Dummy', "verification_message", array(
      'content' => '<div class="tip"><span id="verificationmessage"></span></div>',
    ));
    
    $this->addElement('Text', "code", array(
      'label' => 'Verification Code',
      'description' => '',
      'allowEmpty' => true,
      'required' => false,
    ));

    // Buttons
    $this->addElement('Button', 'submit_code', array(
      'label' => 'Save Changes',
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
    $this->addDisplayGroup(array('submit_code', 'cancel'), 'buttons');
    $button_group = $this->getDisplayGroup('buttons');
  }
}
