<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
abstract class Core_Model_Item_DbTable_Abstract extends Engine_Db_Table
{
  protected $_itemType;

  protected $_localItemCache = array();

  public $_excludedLevels = array(1, 2, 3);   // level_id of Superadmin, Admin & Moderator
  public function __construct($config = array())
  {
    if( !isset($this->_rowClass) ) {
      $this->_rowClass = Engine_Api::_()->getItemClass($this->getItemType());
    }

    // @todo stuff
    parent::__construct($config);
  }

  public function getItemType()
  {
    if( null === $this->_itemType )
    {
      // Try to singularize item table class
      $segments = explode('_', get_class($this));
      $pluralType = array_pop($segments);
      $type = rtrim($pluralType, 's');
      if( !Engine_Api::_()->hasItemType($type) ) {
        $type = rtrim($pluralType, 'e');
        if( !Engine_Api::_()->hasItemType($type) ) {
          throw new Core_Model_Item_Exception('Unable to get item type from dbtable class: '.get_class($this));
        }
      }

      // Make sure we have a column matching
      $prop = $type . '_id';
      if( !in_array($prop, $this->info('cols')) )
      {
        throw new Core_Model_Item_Exception('Unable to get item type from dbtable class: '.get_class($this));
      }

      // Cool
      $this->_itemType = $type;
    }

    return $this->_itemType;
  }

  public function getItem($identity)
  {
    if( !array_key_exists((int) $identity, $this->_localItemCache) )
    {
      $this->_localItemCache[$identity] = $this->find($identity)->current();
    }

    return $this->_localItemCache[$identity];
  }

  public function getItemMulti(array $identities)
  {
    $todo = array();
    foreach( $identities as $identity )
    {
      if( !array_key_exists((int) $identity, $this->_localItemCache) )
      {
        $todo[] = $identity;
      }
    }

    if( count($todo) > 0 )
    {
      foreach( $this->find($todo) as $item )
      {
        $this->_localItemCache[$item->getIdentity()] = $item;
      }
    }

    $ret = array();
    foreach( $identities as $identity )
    {
      $ret[] = $this->_localItemCache[$identity];
    }

    return $ret;
  }

  public function getItemsSelect($params, $select = null)
  {
    if( $select == null ) {
      $select = $this->select();
    }
    $table = $this->info('name');
    $registeredPrivacy = array('everyone', 'registered');
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() && !in_array($viewer->level_id, $this->_excludedLevels) ) {
      $viewerId = $viewer->getIdentity();
      $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
      if( !empty($viewerNetwork) ) {
        array_push($registeredPrivacy,'owner_network');
      }

      $friendsIds = $viewer->membership()->getMembersIds();
      $friendsOfFriendsIds = $friendsIds;
      foreach( $friendsIds as $friendId ) {
        $friend = Engine_Api::_()->getItem('user', $friendId);
        $friendMembersIds = $friend->membership()->getMembersIds();
        $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
      }
    }

    if( !$viewer->getIdentity() ) {
      $select->where("view_privacy = ?", 'everyone');
    } elseif( !in_array($viewer->level_id, $this->_excludedLevels) ) {
      $select->Where("$table.owner_id = ?", $viewerId)
        ->orwhere("view_privacy IN (?)", $registeredPrivacy);
      if( !empty($friendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member' AND $table.owner_id IN (?)", $friendsIds);
      }
      if( !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member_member' AND $table.owner_id IN (?)", $friendsOfFriendsIds);
      }
      if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_network' AND $table.owner_id IN (?)", $friendsOfFriendsIds);
      }

      $subquery = $select->getPart(Zend_Db_Select::WHERE);
      $select ->reset(Zend_Db_Select::WHERE);
      $select ->where(implode(' ',$subquery));
    }

    if( isset($params['search']) ) {
      $select->where("search = ?", $params['search']);
    }
    return $select;
  }

  public function getProfileItemsSelect($owner, $select = null)
  {
    if( $select == null ) {
      $select = $this->select();
    }
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    if( !empty($owner) ) {
      $ownerId = $owner->getIdentity();
    }

    $isOwnerOrAdmin = false;
    if( !empty($viewerId) && ($ownerId == $viewerId || in_array($viewer->level_id, $this->_excludedLevels)) ) {
      $isOwnerOrAdmin = true;
    }

    if( !empty($owner) && $owner instanceof Core_Model_Item_Abstract ) {
      $select
        ->where('owner_id = ?', $ownerId)
        ->order('modified_date DESC')
        ;

      if( $isOwnerOrAdmin ) {
        return $select;
      }

      $isOwnerViewerLinked = true;

      if( $viewer->getIdentity() ) {
        $restrictedPrivacy = array('owner');

        $ownerFriendsIds = $owner->membership()->getMembersIds();
        if( !in_array($viewerId, $ownerFriendsIds) ) {
          array_push($restrictedPrivacy, 'owner_member');

          $friendsOfFriendsIds = array();
          foreach( $ownerFriendsIds as $friendId ) {
            $friend = Engine_Api::_()->getItem('user', $friendId);
            $friendMembersIds = $friend->membership()->getMembersIds();
            $friendsOfFriendsIds = array_merge($friendsOfFriendsIds, $friendMembersIds);
          }

          if( !in_array($viewerId, $friendsOfFriendsIds) ) {
            array_push($restrictedPrivacy, 'owner_member_member');

            $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
            $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
            $ownerNetwork = $netMembershipTable->getMembershipsOfIds($owner);
              $checkViewer = array_intersect($viewerNetwork, $ownerNetwork);
            if( empty($checkViewer) ) {
              $isOwnerViewerLinked = false;
            }
          }
        }
        if( $isOwnerViewerLinked ) {
          $select->where("view_privacy NOT IN (?)", $restrictedPrivacy);
          return $select;
        }
      }

      $select->where("view_privacy = ?", 'everyone');
    }

    return $select;
  }

  public function getAuthorisedSelect($select)
  {
    $authorisedSelect = array();
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return $select;
    }

    $netMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
    $viewerNetwork = $netMembershipTable->getMembershipsOfIds($viewer);
    if( empty($viewerNetwork) ) {
      return $select;
    }

    // authorizing viewer privilege for 'owner_network' privacy
    foreach( $select->getTable()->fetchAll($select) as $item ) {
      if( $item->view_privacy== 'owner_network' && !in_array($viewer->level_id, $this->_excludedLevels) ) {
        if( Engine_Api::_()->authorization()->isAllowed($item, $viewer, 'view') ) {
          $authorisedSelect[] = $item;
        }
      } else {
        $authorisedSelect[] = $item;
      }
    }
    return $authorisedSelect;
  }
}
