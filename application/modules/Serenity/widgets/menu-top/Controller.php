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
class Serenity_Widget_MenuTopController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
   		$api = Engine_Api::_()->serenity();
	   	$api = Engine_Api::_()->serenity();
	    $this->view->contrast_mode = $api->getContantValueXML('contrast_mode') ? $api->getContantValueXML('contrast_mode') : 'dark_mode';
  }
}
