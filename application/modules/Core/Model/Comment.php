<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Comment.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Model_Comment extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;

    public function getHref()
    {
        // @todo take directly to the comment
        if( isset($this->resource_type) ) {
            $resource = Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
            if( $resource ) {
                return $resource->getHref() . '#comment-' . $this->comment_id;
            } else {
                return null;
            }
        } else if( method_exists($this->getTable(), 'getResourceType') ) {
            $tmp = Engine_Api::_()->getItem($this->getTable()->getResourceType(), $this->resource_id);
            if( $tmp ) {
                return $tmp->getHref() . '#comment-' . $this->comment_id;
            } else {
                return null;
            }
        } else {
            return parent::getHref(); // @todo fix this
        }
    }

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

    public function getResource()
    {
        return Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
    }

    public function getAuthorizationItem()
    {
        if( isset($this->resource_type) ) {
            return Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
        } else if( method_exists($this->getTable(), 'getResourceType') ) {
            $tmp = Engine_Api::_()->getItem($this->getTable()->getResourceType(), $this->resource_id);
            return $tmp->getAuthorizationItem(); // Sigh
        } else {
            return $this;
        }
    }

    public function likes()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'core'));
    }

    public function tags()
    {
        return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
    }

    // pre-delete hook
    protected function _delete()
    {
        $resource = Engine_Api::_()->getItem($this->resource_type, $this->resource_id);
        if( isset($resource->comment_count) && $resource->comment_count > 0 ) {
            $resource->comment_count--;
            $resource->save();
        }
        parent::_delete();
    }

    protected function _postInsert()
    {
        $commentedItem = $this->getResource();
        $poster = $this->getPoster();
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $owner = $commentedItem->getOwner();
        if( $owner->getType() != 'user' || $owner->getIdentity() == $poster->getIdentity() ) {
            return;
        }
        $body = $this->body;
        $action = $activityApi->addActivity($poster, $commentedItem, 'comment_' . $commentedItem->getType(), $body, array(
            'owner' => $owner->getGuid(),
            'body' => $body,
            'privacy' => isset($commentedItem->networks) ? 'network_'. implode(',network_', explode(',',$commentedItem->networks)) : null,
        ));
        if( $action ) {
            $activityApi->attachActivity($action, $commentedItem);
        }

        parent::_postInsert();
    }

    protected function _postDelete()
    {
        $commentedItem = $this->getResource();
        $poster = $this->getPoster();
        $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
        $activityApi->removeActivities($poster, $commentedItem, 'comment_' . $commentedItem->getType());
        parent::_postDelete();
    }
}
