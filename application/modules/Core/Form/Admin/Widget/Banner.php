<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Widget_Banner extends Core_Form_Admin_Widget_Standard
{

  public function init()
  {
    parent::init();

    // Set form attributes
    $this
      ->setTitle('Display Banner')
      ->setDescription('Please choose an banner.');
    $table = Engine_Api::_()->getDbtable('banners', 'core');
    $banners = $table->fetchAll($table->getBannersSelect());

    $this->removeElement('title');

    if( count($banners) > 0 ) {
      $this->addElement('Select', 'banner_id', array(
        'label' => 'Banner',
        'allowEmpty' => false,
        'required' => true,
        'validators' => array(
          array('NotEmpty', true),
        )
      ));

      $this->banner_id->addMultiOption(0, '');
      foreach( $banners as $banner ) {
        $this->banner_id->addMultiOption($banner->getIdentity(), $banner->getTitle());
      }
    }
  }
}
