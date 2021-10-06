<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Adcampaign.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_Adcampaign extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;
  
  public function getAdCount()
  {
    $table = Engine_Api::_()->getDbtable('Ads', 'core');
    return $table->select()
        ->from($table, new Zend_Db_Expr('COUNT(ad_id)'))
        ->where('ad_campaign = ?', $this->adcampaign_id)
        ->query()
        ->fetchColumn();
  }

  public function getAds()
  {
    $table = Engine_Api::_()->getDbtable('Ads', 'core');
    $select = $table->select()->where('ad_campaign = ?', $this->adcampaign_id);
    return $table->fetchAll($select);
  }

  public function getAd()
  {
    $table = Engine_Api::_()->getDbtable('Ads', 'core');
    $select = $table->select()->where('ad_campaign = ?', $this->adcampaign_id)->order('RAND()')->limit(1);
    return $table->fetchRow($select);
  }


  
  // Info

  public function isAllowedToView(User_Model_User $user)
  {
    $isMemberAllowed = false;
    $isMemberLevelEmpty = false;

    // Check level
    $selectedLevels = Zend_Json::decode($this->level);
    if( !empty($selectedLevels) && is_array($selectedLevels) ) {
      // Check for public level user
      if( !$user->getIdentity() ) {
        $levelIdentity = Engine_Api::_()->getDbtable('levels', 'authorization')->getPublicLevel()->level_id;
        return in_array($levelIdentity, $selectedLevels);
      }
      $isMemberAllowed = in_array($user->level_id, $selectedLevels);
      if( $isMemberAllowed && $this->target_member ) {
        return $isMemberAllowed;
      }
    } else {
      $isMemberLevelEmpty = true;
    }

    $selectedNetworks = Zend_Json::decode($this->network);
    if( empty($selectedNetworks) || !is_array($selectedNetworks) ) {
      return $isMemberAllowed;
    }
    // Check network
    $userNetworks = Engine_Api::_()->getDbtable('membership', 'network')
      ->getMembershipsOfIds($user, null);

    $isNetworkAllowed = count(array_intersect($userNetworks, $selectedNetworks)) > 0;

    if( $isMemberLevelEmpty ) {
      return $isNetworkAllowed;
    }

    // return true if any 2 of 3 are true else false
    return ($isMemberAllowed == $isNetworkAllowed) ? $isMemberAllowed : $this->target_member;
  }

  public function isActive()
  {
    return (
       $this->status &&
       $this->hasStarted() &&
      !$this->hasExpired() &&
      !$this->hasReachedClickLimit() &&
      !$this->hasReachedCtrLimit() &&
      !$this->hasReachedViewLimit()
    );
  }

  public function hasStarted()
  {
    return (time() > strtotime($this->start_time));
  }

  public function hasExpired()
  {
    return ($this->end_settings == 1) && (time() > strtotime($this->end_time));
  }

  public function hasReachedViewLimit()
  {
    return !empty($this->limit_view) &&
        ($this->views >= $this->limit_view);
  }

  public function hasReachedClickLimit()
  {
    return !empty($this->limit_click) &&
        $this->clicks >= $this->limit_click;
  }

  public function hasReachedCtrLimit()
  {
    return !empty($this->limit_ctr) &&
        ($this->views > 0) &&
        ($this->clicks / $this->views * 100) <= $this->limit_ctr;
  }



  // B/c

  public function allowedToView(User_Model_User $user)
  {
    return $this->isAllowedToView($user);
  }

  public function checkLimits()
  {
    return $this->isActive();
  }

  public function checkStarted()
  {
    return !$this->hasStarted();
  }

  public function checkExpired()
  {
    return $this->hasExpired();
  }
}
