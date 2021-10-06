<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: MapProfileTypeLevel.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */
class Authorization_Model_MapProfileTypeLevel extends Core_Model_Item_Abstract
{
  protected $_disableHooks = true;

  public function getMembershipCount($member_level_id)
  {
    $user_field_value_table = Engine_Api::_()->getItemTable('value');
    $user_field_value_name = $user_field_value_table->info('name');
    $user_table = Engine_Api::_()->getItemTable('user');
    $user_name = $user_table->info('name');
    $select = $user_field_value_table->select()
      ->setIntegrityCheck(false)
      ->from($user_field_value_name, array('item_id'))
      ->joinInner($user_name, "$user_field_value_name . item_id = $user_name . user_id", array("level_id"))
      ->where('value = ?', $this->profile_type_id)
      ->where('field_id = ?', 1);
    $rows = $select->query()->fetchAll();
    $memberCount = 0;
    foreach ($rows as $value) {
      if ($value['level_id'] == $member_level_id) {
        $memberCount++;
      }
    }

    return $memberCount;
  }
}
