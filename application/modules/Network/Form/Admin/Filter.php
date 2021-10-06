<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Filter.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Network
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Network_Form_Admin_Filter extends Engine_Form
{
  public function init()
  {
    $this
      ->setMethod('GET')
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ));

    $this->addElement('Hidden', 'order', array(
      'order' => 1
    ));

    $this->addElement('Hidden', 'direction', array(
      'order' => 2
    ));
  }
}
