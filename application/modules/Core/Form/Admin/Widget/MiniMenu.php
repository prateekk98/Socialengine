<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Widget_MiniMenu extends Core_Form_Admin_Widget_Standard
{
  public function init()
  {
    parent::init();

    // Set form attributes
    $this
      ->setAttrib('id', 'form-upload');

    $themes = Engine_Api::_()->getDbtable('themes', 'core')->fetchAll();
    $activeTheme = $themes->getRowMatching('active', 1);
		
		// Element: show_icons	
		$this->addElement('Radio', 'show_icons', array(	
			'label' => 'Choose menu display type below:',	
			'multiOptions' => array(	
				1 => 'Show only Icon',	
				0 => 'Show only Label',	
			),	
			'value' => 1,	
    ));	
  }
}
