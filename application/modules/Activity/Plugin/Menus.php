<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9770 2012-08-30 02:36:05Z richard $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Plugin_Menus
{
  // core_main
  public function onMenuInitialize_CoreMiniUpdate($row)
  {
    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    $notificationCount = Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($viewer);
    $view = Zend_Registry::get('Zend_View');
    $label = $view->translate(array('%s Update', '%s Updates', $notificationCount), $view->locale()->toNumber($notificationCount));
    return array(
      'label' => $label,
      'class' => 'updates_toggle ' . ( $notificationCount ? 'new_updates' : ''),
      'uri' => 'javascript:void(0);this.blur();',
    );
  }

}
