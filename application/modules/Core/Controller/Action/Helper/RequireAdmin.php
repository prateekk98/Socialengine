<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: RequireAdmin.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Controller_Action_Helper_RequireAdmin extends
  Core_Controller_Action_Helper_RequireAbstract
{
  protected $_errorAction = array('requireadmin', 'error', 'core');

  public function checkRequire()
  {
    if( defined('_ENGINE_ADMIN_NEUTER') && _ENGINE_ADMIN_NEUTER ) {
      return true;
    }

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      $ret = $viewer->isAllowed('admin');
    }
    catch( Exception $e )
    {
      $ret = false;
    }


    if( !$ret && APPLICATION_ENV == 'development' && Zend_Registry::isRegistered('Zend_Log') && ($log = Zend_Registry::get('Zend_Log')) instanceof Zend_Log )
    {
      $target = $this->getRequest()->getModuleName() . '.' .
              $this->getRequest()->getControllerName() . '.' .
              $this->getRequest()->getActionName();
      $log->log('Require class '.get_class($this).' failed check for: '.$target, Zend_Log::DEBUG);
    }

    return $ret;
  }
}
