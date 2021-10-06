<?php

/**
 * SocialEngine - Search Widget Controller
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2012 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     Matthew
 */
class Core_Widget_SearchMiniController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    if(Engine_Api::_()->getDbTable('modules', 'core')->isModuleEnabled('serenity')) {
      $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
      $headerloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headerloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      $headernonloggedinoptions = unserialize(Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.headernonloggedinoptions', 'a:4:{i:0;s:6:"search";i:1;s:8:"miniMenu";i:2;s:8:"mainMenu";i:3;s:4:"logo";}'));
      if(!empty($viewer_id))
        empty($headerloggedinoptions) ? $this->setNoRender() : (!in_array('search', $headerloggedinoptions)) ? $this->setNoRender() : '';
      else 
        empty($headernonloggedinoptions) ? $this->setNoRender() : (!in_array('search', $headernonloggedinoptions)) ? $this->setNoRender() : '';
    }
    
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $controllerName = $request->getControllerName();
    $actionName = $request->getActionName();
    if($controllerName == 'signup') {
      return $this->setNoRender();
    } else if($actionName == 'login') {
      return $this->setNoRender();
    }
    
    $requireCheck = Engine_Api::_()->getApi('settings', 'core')->core_general_search;
    if( !$requireCheck && !Zend_Controller_Action_HelperBroker::getStaticHelper('RequireUser')->checkRequire() ) {
      $this->setNoRender();
      return;
    }
  }
}
