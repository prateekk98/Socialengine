<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Packages.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Model_DbTable_Packages extends Engine_Db_Table
{
  protected $_rowClass = 'Payment_Model_Package';

  public function getEnabledPackages($isSignUp = false)
  {
    $select = $this->select()->where('enabled = ?', true);

    $accountSession = new Zend_Session_Namespace('User_Plugin_Signup_Account');
    $profileTypeValue = @$accountSession->data['profile_type'];
    $mappedLevel = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')->getMappedLevelId($profileTypeValue);
    if (!empty($mappedLevel)) {
      $select->where('level_id = ?', $mappedLevel);
    }

    if ($isSignUp) {
      $select->where('signup = ?', true);
    } else {
      $select->where('after_signup = ?', true);
    }

    return $this->fetchAll($select);
  }

  public function getDefaultPackage()
  {
    $select = $this->select()->where($this->info('name').'.enabled = ?', 1);
    $select->where($this->info('name').'.default = ?', 1);  
    return $this->fetchRow($select);
  }

  public function getEnabledPackageCount()
  {
    return $this->select()
      ->from($this, new Zend_Db_Expr('COUNT(*)'))
      ->where('enabled = ?', 1)
      ->query()
      ->fetchColumn()
      ;
  }

  public function getEnabledNonFreePackageCount()
  {
    return $this->select()
      ->from($this, new Zend_Db_Expr('COUNT(*)'))
      ->where('enabled = ?', 1)
      ->where('price > ?', 0)
      ->query()
      ->fetchColumn()
      ;
  }
}
