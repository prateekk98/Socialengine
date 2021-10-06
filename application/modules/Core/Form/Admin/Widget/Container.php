<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Ali Mousavi ( ali@socialengine.com )
 */

class Core_Form_Admin_Widget_Container extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();
      
    // Hide title field
    $this->addElement('Hidden', 'title', array(
    ));

    $this->addElement('Select', 'max', array(
      'label' => 'Max Tab Count',
      'description' => 'Show sub menu at x containers.',
      'value' => 4,
      'multiOptions' => array(
        0 => 0,
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
      )
    ));
  }
}
