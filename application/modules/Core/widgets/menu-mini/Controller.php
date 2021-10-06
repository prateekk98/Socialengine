<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_MenuMiniController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('serenity')) {
      $viewer_id = $viewer->getIdentity();
      $headerloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        empty($headerloggedinoptions) ? $this->setNoRender() : (!in_array('miniMenu', $headerloggedinoptions)) ? $this->setNoRender() : '';
      else 
        empty($headernonloggedinoptions) ? $this->setNoRender() : (!in_array('miniMenu', $headernonloggedinoptions)) ? $this->setNoRender() : '';
    }
    
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('core_mini');
      
    $this->view->settingNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_settings', array());

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->notificationOnly = $request->getParam('notificationOnly', false);
    $this->view->updateSettings = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.notificationupdate');
    $this->view->showIcons = $this->_getParam('show_icons', 1);
    $this->view->message_count = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);
  }
}
