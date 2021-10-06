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
class Core_Widget_MenuMainController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('serenity')) {
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $headerloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        empty($headerloggedinoptions) ? $this->setNoRender() : (!in_array('mainMenu', $headerloggedinoptions)) ? $this->setNoRender() : '';
      else 
        empty($headernonloggedinoptions) ? $this->setNoRender() : (!in_array('mainMenu', $headernonloggedinoptions)) ? $this->setNoRender() : '';
    }
    
    $this->view->navigation = $navigation = Engine_Api::_()
      ->getApi('menus', 'core')
      ->getNavigation('core_main');

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    $requireCheck = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.browse', 1);
    if( !$requireCheck && !$viewer->getIdentity() ) {
      $navigation->removePage($navigation->findOneBy('route', 'user_general'));
    }
    $this->view->menuType = $menuType = $this->_getParam('menuType', 'horizontal');
    $this->view->menuFromTheme = $this->_getParam('menuFromTheme', false);
    $this->view->menuCount = '999';
    $this->view->submenu = $this->_getParam("submenu",1);
    if($menuType == 'horizontal') {
      $this->view->menuCount = $this->_getParam('menuCount', 0) ? $this->_getParam('menuCount', 0) : '999';
    }
  }

  public function getCacheKey()
  {
    //return Engine_Api::_()->user()->getViewer()->getIdentity();
  }
}
