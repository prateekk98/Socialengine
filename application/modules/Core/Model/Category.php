<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Comment.php 9747 2016-12-14 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_Category extends Core_Model_Item_Abstract
{
  public function getHref($params = array())
  {
    return Zend_Controller_Front::getInstance()->getRouter()
            ->assemble($params, $this->_route, true) . '?category_id=' . $this->category_id;
  }
}
