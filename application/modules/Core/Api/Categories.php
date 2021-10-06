<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Categories.php 9747 2016-12-15 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Api_Categories extends Core_Api_Abstract
{
  public function getNavigation($module, array $options = array())
  {
    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $activeItem = null;
    if( isset($params['category_id']) ) {
      $activeItem = $params['category_id'];
    } elseif( isset($params['category']) ) {
      $activeItem = $params['category'];
    }
    $pages = $this->getCategoryParams($module, $options, $activeItem);
    $navigation = new Zend_Navigation();
    $navigation->addPages($pages);
    return $navigation;
  }

  public function getCategoryParams($module, array $options = array(), $activeItem = null)
  {
    $category = $this->getCategory($module);
    $pages = array();
    $count = 0;

    foreach( $category as $row ) {
      $page = null;

      // Add label
      $page['label'] = $row->getTitle();

      // Add type for URI
      $page['type'] = 'uri';
      $page['uri'] = $row->getHref();

      // Set page as active, if necessary
      if( null !== $activeItem && $activeItem == $row->category_id ) {
        $page['active'] = true;
      }

      $page['class'] = (!empty($page['class']) ? $page['class'] . ' ' : '' ) . 'category_' . $module;
      $page['class'] .= " category_" . str_replace('-', '_', $row->getSlug());

      // Maintain category item order
      if( isset($row->order) ) {
        $page['order'] = $row->order;
      } else {
        $page['order'] = $count;
        $count++;
      }

      $pages[] = $page;
    }

    return $pages;
  }

  public function getCategory($module)
  {
    $categoriesTable = Engine_Api::_()->getDbtable('categories', $module);
    return $categoriesTable->fetchAll();
  }

}
