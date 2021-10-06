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
class Core_Widget_MenuLogoController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('serenity')) {
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $headerloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        empty($headerloggedinoptions) ? $this->setNoRender() : (!in_array('logo', $headerloggedinoptions)) ? $this->setNoRender() : '';
      else 
        empty($headernonloggedinoptions) ? $this->setNoRender() : (!in_array('logo', $headernonloggedinoptions)) ? $this->setNoRender() : '';
    }
    
    $this->view->logo = $this->_getParam('logo');
    $this->view->disableLink = $this->_getParam('disableLink',0);
  }

  public function getCacheKey()
  {
    //return true;
  }
}
