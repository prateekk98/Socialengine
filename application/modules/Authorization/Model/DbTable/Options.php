<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: Options.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 */
class Authorization_Model_DbTable_Options extends Engine_Db_Table
{
  protected $_name = 'user_fields_options';
  protected $_rowClass = 'Authorization_Model_Option';

  public function getAllProfileTypes() {
    $select = $this->select()
      ->where('field_id = ?', 1);
    $result = $this->fetchAll($select);
    return $result;
  }

  public function getProfileTypesOptions() {
    $profileTypes = $this->getAllProfileTypes();
    $profileTypeIds = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')->getMappedProfileTypeIds();
    foreach ($profileTypes as $profileType) {
      $showOption = true;
      foreach ($profileTypeIds as $profileTypeId) {
        if ($profileType->option_id == $profileTypeId['profile_type_id']) {
          $showOption = false;
          break;
        }
      }
      if ($showOption) {
        $profileTypeOptions[$profileType->option_id] = $profileType->label;
      }
    }
    return $profileTypeOptions;
  }
}
