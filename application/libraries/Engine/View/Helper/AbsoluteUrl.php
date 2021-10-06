<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Item.php 9747 2016-11-30 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_AbsoluteUrl extends Zend_View_Helper_Abstract
{
  public function absoluteUrl($url)
  {
    $host = parse_url($url, PHP_URL_HOST);
    if( $host !== null ) {
      return $url;
    }

    return $this->view->serverUrl($url);
  }
}
