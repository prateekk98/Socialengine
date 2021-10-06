<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Birthdate.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Admin_Field_Birthdate extends Fields_Form_Admin_Field
{
  public function init()
  {
    parent::init();

    // Add minimum age
    $this->addElement('Integer', 'min_age', array(
      'label' => 'Minimum Age',
    ));

    $this->addElement('Select', 'birthday_format', array(
      'label' => 'Default Birthday Format',
       'multiOptions' => array(
            'monthday' => 'Month/Day',
            'monthdayyear' => 'Month/Day/Year',
        ),
       'value'=>'monthdayyear',
    ));
  }
}
