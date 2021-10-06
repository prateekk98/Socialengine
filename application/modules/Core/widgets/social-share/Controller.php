<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2016-11-11 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_SocialShareController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    $socialCode = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.social.code');
    $socialCode = trim($socialCode);
    if( empty($socialCode) ) {
      return $this->setNoRender();
    }
    $this->view->socialCode = $socialCode;
  }
}
