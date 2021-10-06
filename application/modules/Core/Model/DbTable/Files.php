<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Files.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Model_DbTable_Files extends Engine_Db_Table {

  protected $_rowClass = 'Core_Model_File';
  
  public function getFileNameExist($file_name) {
    return $this->select()->from($this->info('name'), 'file_id')->where('name =?', $file_name)->query()->fetchColumn();
  }
  
  public function getPaginator($params = array()) {
    return Zend_Paginator::factory($this->getFiles($params));
  }
  
  public function getFiles($params = array()) {

    $select = $this->select()->order('file_id DESC');
    
    if(!empty($params['extension']) && isset($params['extension']))
      $select->where('extension IN (?)', $params['extension']);
    
    if(!empty($params['name']) && isset($params['name']))
      $select->where("name LIKE ?", '%'.$params['name'].'%');

    if(!empty($params['fetchAll']))
      return $this->fetchAll($select);

    return $select;
  }
}
