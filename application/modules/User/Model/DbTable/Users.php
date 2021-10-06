<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Users.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Model_DbTable_Users extends Engine_Db_Table
{
  protected $_name = 'users';

  protected $_rowClass = 'User_Model_User';
  public function getAllAdmin()
  {
  	$levelTable = Engine_Api::_()->getDbtable('levels', 'authorization');
  	$levelTableName = $levelTable->info("name");
  	$tableName = $this->info("name");
  	$select = $this->select()->setIntegrityCheck(false)
  		->from($tableName)
  		->joinLeft($levelTableName, "$levelTableName.level_id = $tableName.level_id",null)
  		->where($levelTableName.".type = ?","admin");
  	return $this->fetchAll($select);
  }
}
