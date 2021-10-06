<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Like.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_Like extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;

    public function getOwner($type = null)
    {
        $poster = $this->getPoster();
        if( null === $type && $type !== $poster->getType() ) {
            return $poster->getOwner($type);
        }
        return $poster;
    }

    public function getPoster()
    {
        return Engine_Api::_()->getItem($this->poster_type, $this->poster_id);
    }

    public function __toString()
    {
        return $this->getPoster()->__toString();
    }

    // pre-delete hook
    protected function _delete()
    {
        $resource = Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
        if( isset($resource->like_count) && $resource->like_count > 0 ) {
            $resource->like_count--;
            $resource->save();
        }
        parent::_delete();
    }

    public function getResource()
    {
        return Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
    }

    protected function _postInsert()
    {
        $likedItem = $this->getResource();
        $poster = $this->getPoster();
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $owner = $likedItem->getOwner();
        if( $owner->getType() != 'user' || $owner->getIdentity() == $poster->getIdentity() ) {
            return;
        }
        
        if(isset($likedItem->type) && $likedItem->type == 'event_create') {
          $likedItem = Engine_Api::_()->getItem($likedItem->object_type, $likedItem->object_id);
          $owner = $likedItem->getOwner();
        } else if(isset($likedItem->type) && $likedItem->type == 'group_create') {
          $likedItem = Engine_Api::_()->getItem($likedItem->object_type, $likedItem->object_id);
          $owner = $likedItem->getOwner();
        }

        $action = $activityApi->addActivity($poster, $likedItem, 'like_' . $likedItem->getType(), '', array(
            'owner' => $owner->getGuid(),
            //'privacy' => isset($likedItem->networks) ? 'network_'. implode(',network_', explode(',',$likedItem->networks)) : null,
        ));
        if( $action ) {
            $activityApi->attachActivity($action, $likedItem);
        }

        parent::_postInsert();
    }

    protected function _postDelete()
    {
        $likedItem = $this->getResource();
        $poster = $this->getPoster();
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $activityApi->removeActivities($poster, $likedItem, 'like_' . $likedItem->getType());

        parent::_postDelete();
    }
}
