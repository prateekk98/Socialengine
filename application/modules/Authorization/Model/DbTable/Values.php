<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: Values.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */
class Authorization_Model_DbTable_Values extends Engine_Db_Table
{
  protected $_name = 'user_fields_values';
  protected $_rowClass = 'Authorization_Model_Value';

  public function resetProfileValues($user)
  {
    $this->delete(array('item_id = ?' => $user->getIdentity()));
  }

  public function changeUsersProfileType($user)
  {
    $profiletypes_array = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')
      ->getMappedProfileTypeIds($user->level_id);
    if (count($profiletypes_array) < 1) {
      return false;
    }

    //Get User's profile type
    $select = $this->select()
      ->where('item_id = ?', $user->getIdentity())
      ->where('field_id = ?', 1)
      ->limit(1);

    $profileType = $select->query()->fetchAll();
    if (empty($profileType)) {
      return true;
    }

    foreach($profiletypes_array as $value) {
      if ($profileType[0]['value'] == $value['profile_type_id']) {
        return false;
      }
    }
    return true;
  }

  public function getProfileTypeUsers($profileTypeId)
  {
    $select = $this->select()
      ->from($this->info('name'), array('item_id'))
      ->where('field_id = ?', 1)
      ->where('value = ?', $profileTypeId);
    return $select->query()->fetchAll();
  }

  public function getUsersFromMapping($mappingIds)
  {
    $userValueTableName = $this->info('name');
    $mapprofile_table_name = Engine_Api::_()->getItemTable('mapProfileTypeLevel')->info('name');
    $userTable = Engine_Api::_()->getItemTable('user');
    $userTableName = $userTable->info('name');
    $select = $this->select()
      ->from($userValueTableName, array('item_id', 'value'))
      ->joinInner($userTableName, "$userValueTableName . item_id = $userTableName . user_id", array())
      ->joinInner($mapprofile_table_name, "$userValueTableName . value = $mapprofile_table_name . profile_type_id", array())
      ->where("$userValueTableName.field_id = ?", 1)
      ->where("$mapprofile_table_name.mapprofiletypelevel_id IN ($mappingIds)");
    return $select->query()->fetchAll();
  }
}
