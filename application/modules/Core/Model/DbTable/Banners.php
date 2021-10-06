<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: MenuItems.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_DbTable_Banners extends Engine_Db_Table
{

  protected $_serializedColumns = array('params');
  protected $_rowClass = "Core_Model_Banner";

  /**
   * Get paginator for banners
   *
   * @param array
   * @return Zend_Paginator
   */
  public function getBannersPaginator($params = array())
  {
    $paginator = Zend_Paginator::factory($this->getBannersSelect());
    if( !empty($params['page']) ) {
      $paginator->setCurrentPageNumber($params['page']);
    }
    if( !empty($params['limit']) ) {
      $paginator->setItemCountPerPage($params['limit']);
    }
    return $paginator;
  }

  /**
   * Gets a select object for the banners entries
   *
   * @return Zend_Db_Table_Select
   */
  public function getBannersSelect()
  {
    $select = $this->select();
    $enabledModuleNames = Engine_Api::_()->getDbtable('modules', 'core')->getEnabledModuleNames();
    if( !empty($enabledModuleNames) ) {
      $select->where('module IN(?)', $enabledModuleNames);
    }
    return $select;
  }

  public function getBanner($id)
  {
    $select = $this->getBannersSelect()->where('banner_id = ?', $id);
    return $this->fetchRow($select);
  }

}
