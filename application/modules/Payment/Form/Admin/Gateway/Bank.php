<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Bank.php 9747 2019-12-07 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Admin_Gateway_Bank extends Payment_Form_Admin_Gateway_Abstract
{
  public function init()
  {
    parent::init();
    $this->setTitle('Payment Gateway: Bank');
    $this->setDescription('PAYMENT_FORM_ADMIN_GATEWAY_BANK_DESCRIPTION');
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

  $checkInfo = "
  Account Name:
Account Number:
Bank Name:
Branch Address of Bank:
IFSC Code:
  ";


    // Elements
    $this->addElement('Textarea', 'account_details', array(
      'label' => 'Account Details',
      'required' => true,
      'allowEmpty' => false,
      'value'=>$checkInfo,
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
    $this->addElement('Select', 'receipt', array(
      'label' => 'Make Receipt Upload Mandatory',
      'description'=> 'Do you want to make receipt upload field mandatory when users go for payment via bank?',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' =>array('1'=>'Yes','0'=>'No'),
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));
  }
}
