<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: MapProfileTypeLevels.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */
class Authorization_Model_DbTable_MapProfileTypeLevels extends Engine_Db_Table
{
  protected $_name = 'authorization_mapprofiletypelevels';
  protected $_rowClass = 'Authorization_Model_MapProfileTypeLevel';

  public function getMappedProfileTypeIds($memberLevelId = null) {
    $select = $this->select()->from($this->info('name'), array('profile_type_id','mapprofiletypelevel_id', 'member_level_id'));
    if (!empty($memberLevelId)) {
      $select->where('member_level_id = ?', $memberLevelId);
    }
    return $select->query()->fetchAll();
  }

  public function getMappedLevelId($profileTypeId = null) {
    if (empty($profileTypeId)) {
      return;
    }

    $select = $this->select()->from($this->info('name'), array('member_level_id'));
    $select->where('profile_type_id = ?', $profileTypeId);
    return $select->query()->fetchColumn();
  }

  public function getMappingId($profileTypeId = null) {
    if (empty($profileTypeId)) {
      return;
    }

    $select = $this->select()->from($this->info('name'), array('mapprofiletypelevel_id'));
    $select->where('profile_type_id = ?', $profileTypeId);
    return $select->query()->fetchColumn();
  }
}
