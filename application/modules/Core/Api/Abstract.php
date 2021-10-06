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
abstract class Core_Api_Abstract
{
  protected $_moduleName;

  protected $_excludedLevels = array(1, 2, 3);   // level_id of Superadmin,Admin & Moderator

  public function getModuleName()
  {
    if( empty($this->_moduleName) )
    {
      $class = get_class($this);
      if (preg_match('/^([a-z][a-z0-9]*)_/i', $class, $matches)) {
        $prefix = $matches[1];
      } else {
        $prefix = $class;
      }
      // @todo sanity
      $this->_moduleName = strtolower($prefix);
    }
    return $this->_moduleName;
  }

  public function __call($method, array $arguments = array())
  {
    throw new Engine_Exception(sprintf('Method "%s" not supported', $method));
  }

  public function getItemsSelect($select, $params = array())
  {
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
      $select->Where("owner_id = ?", $viewerId)
        ->orwhere("view_privacy IN (?)", $registeredPrivacy);
      if( !empty($friendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member' AND owner_id IN (?)", $friendsIds);
      }
      if( !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_member_member' AND owner_id IN (?)", $friendsOfFriendsIds);
      }
      if( empty($viewerNetwork) && !empty($friendsOfFriendsIds) ) {
        $select->orWhere("view_privacy = 'owner_network' AND owner_id IN (?)", $friendsOfFriendsIds);
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

  public function getProfileItemsSelect($select, $owner)
  {
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
            if( !array_intersect($viewerNetwork, $ownerNetwork) ) {
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
      if( $item->view_privacy == 'owner_network' && !in_array($viewer->level_id, $this->_excludedLevels) ) {
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
