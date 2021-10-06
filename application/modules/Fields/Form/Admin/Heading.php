<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Heading.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Admin_Heading extends Fields_Form_Admin_Field
{
  public function init()
  {
    parent::init();

    $this->setTitle('Add Heading');
    $this->removeElement('description');
    $this->removeElement('error');
    $this->removeElement('required');
    $this->removeElement('icon');
    $this->getElement('label')->setLabel('Heading Label');
    $this->getElement('execute')->setLabel('Save Heading');

    $type = Zend_Controller_Front::getInstance()->getRequest()->getParam('type');
    if( !isset($type) ) {
      $this->removeElement('heading');
      $this->addElement('Hidden', 'type', array(
        'value' => 'heading',
        'order' => 999
      ));
    }
  }
}
