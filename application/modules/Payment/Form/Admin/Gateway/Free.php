<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Free.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Form_Admin_Gateway_Free extends Payment_Form_Admin_Gateway_Abstract
{
  public function init()
  {
    parent::init();
    $this->setTitle('Payment Gateway: Free');
    $this->setDescription('PAYMENT_FORM_ADMIN_GATEWAY_FREE_DESCRIPTION');

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
  }
}
