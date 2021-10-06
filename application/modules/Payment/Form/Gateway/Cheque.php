<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Cheque.php 9747 2019-12-07 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Gateway_Cheque extends Engine_Form
{ 
  protected  $_settings = array();
  public function setSettings($settings) {
    $this->_settings = $settings;
    return $this;
  }
  public function getSettings() {
    return $this->_settings;
  }
  public function init()
  {
    parent::init();
    $this->setTitle('Payment Gateway: Cheque');
    $this->setDescription('PAYMENT_FORM_GATEWAY_CHEQUE_DESCRIPTION');
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    
    $this->addElement('Textarea', 'account_details', array(
      'label' => 'Account Details:',
      'disabled'=>true,
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
    
    $this->addElement('File', 'file', array(
      'label' => 'Upload the receipt of Transaction.',
      'description' => '',
      'required' => $this->_settings['receipt'] ? true : false,
      'priority' => 99998,
    ));
    $this->addElement('Button', 'submit', array(
      'label' => 'Upload',
      'order'=>1001,
      'type' => 'submit',
    ));
  }
}
