<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Action.php 10249 2014-05-30 22:38:38Z andres $
 * @author     John
 * @todo       documentation
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Model_Action extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;

    const ATTACH_IGNORE = 0;
    const ATTACH_NORMAL = 1;
    const ATTACH_MULTI = 2;
    const ATTACH_DESCRIPTION = 3;
    const ATTACH_COLLECTION = 4;
    // Comments linked to object
    protected $_allowCommentableForSimilar = array(3);
    // Share linked to object or not linked to any
    protected $_allowShareableForSimilar = array(0, 3);

    /**
     * The action subject
     *
     * @var Core_Model_Item_Abstract
     */
    protected $_subject;

    /**
     * The action object
     *
     * @var Core_Model_Item_Abstract
     */
    protected $_object;

    /**
     * The action attachments
     *
     * @var mixed
     */
    protected $_attachments;

    /**
     * The action likes
     *
     * @var mixed
     */
    protected $_likes;

    /**
     * The action comments
     *
     * @var mixed
     */
    protected $_comments;



    // General

    public function getHref($params = array())
    {
        $displayable = $this->getTypeInfo()->displayable;
        $params['action_id'] = $this->getIdentity();
        if ($displayable & 2) {
            $obj = $this->getObject();
            return !$obj ? null : $obj->getHref($params);
        } elseif ($displayable & 1) {
            $obj = $this->getSubject();
            return !$obj ? null : $obj->getHref($params);
        } elseif ($displayable & 4) {
            $params['action'] = 'home';
            return Zend_Controller_Front::getInstance()->getRouter()->assemble($params, 'user_general', true);
        } else {
            return null;
        }
    }

    /**
     * Gets an item that defines the authorization permissions, usually the item
     * itself
     *
     * @return Core_Model_Item_Abstract
     */
    public function getAuthorizationItem()
    {
        return $this->getObject();
    }

    public function getParent($recurseType = null)
    {
        return $this->getObject();
    }

    public function getOwner($recurseType = null)
    {
        return $this->getSubject();
    }

    public function getDescription()
    {
        return $this->getContent();
    }

    public function canMakeSimilar()
    {
        $typeInfo = $this->getTypeInfo();
        return (in_array($typeInfo->commentable, $this->_allowCommentableForSimilar)
            && in_array($typeInfo->shareable, $this->_allowShareableForSimilar));
    }

    /**
     * Assembles action string
     *
     * @return string
     */
    public function getContent($others = array())
    {
        $model = Engine_Api::_()->getApi('core', 'activity');
        $params = array_merge(
            $this->toArray(),
            (array) $this->params,
            array(
                'action' => $this,
                'subject' => $this->getSubject(),
                'object' => $this->getObject(),
            )
        );

        $body = $this->getTypeInfo()->body;
        $shouldTranslate = true;
        if (count($others) > 0) {
            $otherText = '{item:$subject} and {others:$otherItems}';
            $translate = Zend_Registry::get('Zend_Translate');
            if ($translate instanceof Zend_Translate) {
                $body = $translate->translate($body);
                $otherText = $translate->translate($otherText);
            }
            $body = str_replace('{item:$subject}', $otherText, $body);
            $shouldTranslate = false;
            $params['otherItems'] = $others;
        }
        $content = $model->assemble($body, $params, $shouldTranslate);
        return $content;
    }

    /**
     * Magic to string {@link self::getContent()}
     * @return string
     */
    public function __toString()
    {
        return $this->getContent();
    }

    /**
     * Get the action subject
     *
     * @return Core_Model_Item_Abstract
     */
    public function getSubject()
    {
        if (null === $this->_subject) {
            $this->_subject = Engine_Api::_()->getItem($this->subject_type, $this->subject_id);
        }

        return $this->_subject;
    }

    /**
     * Get the action object
     *
     * @return Core_Model_Item_Abstract
     */
    public function getObject()
    {
        if (null === $this->_object) {
            try {
                $this->_object = Engine_Api::_()->getItem($this->object_type, $this->object_id);
            } catch (Exception $e) {
                // silence
            }
        }

        return $this->_object;
    }

    /**
     * Get the type info
     *
     * @return Engine_Db_Table_Row
     */
    public function getTypeInfo()
    {
        $info = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($this->type);
        if (!$info) {
            //throw new Exception('Missing Action Type: ' . $this->type);
        }
        return $info;
    }

    /**
     * Get the timestamp
     *
     * @return integer
     */
    public function getTimeValue()
    {
        //$current = new Zend_Date($this->date, Zend_Date::ISO_8601);
        //return $current->toValue();
        return strtotime($this->date);
    }

    public function isViewerLike()
    {
        if ($this->comments()->getLikeCount() <= 0) {
            return false;
        }

        return $this->comments()->isLike(Engine_Api::_()->user()->getViewer());
    }


    // Attachments

    public function attach(Core_Model_Item_Abstract $attachment, $mode = 1)
    {
        return Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($this, $attachment, $mode);
    }

    public function getFirstAttachment()
    {
        list($attachement) = $this->getAttachments();
        return $attachement;
    }

    public function getAttachments()
    {
        if (null !== $this->_attachments) {
            return $this->_attachments;
        }

        if ($this->attachment_count <= 0) {
            return null;
        }

        $table = Engine_Api::_()->getDbtable('attachments', 'activity');
        $select = $table->select()
            ->where('action_id = ?', $this->action_id);

        foreach ($table->fetchAll($select) as $row) {
            $item = Engine_Api::_()->getItem($row->type, $row->id);
            if ($item instanceof Core_Model_Item_Abstract) {
                $val = new stdClass();
                $val->meta = $row;
                $val->item = $item;
                $this->_attachments[] = $val;
            }
        }

        return $this->_attachments;
    }

    public function getLikes()
    {
        if (null !== $this->_likes) {
            return $this->_likes;
        }

        return $this->_likes = $this->likes()->getAllLikes();
    }

    public function getComments($commentViewAll)
    {
        if (null !== $this->_comments) {
            return $this->_comments;
        }

        $comments = $this->comments();
        $table = $comments->getReceiver();
        $commentCount = $comments->getCommentCount();

        if ($commentCount <= 0) {
            return;
        }

        $reverseOrder = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.commentreverseorder', false);

        // Always just get the last three comments
        $select = $comments->getCommentSelect();

        if ($commentCount <= 5) {
            $select->limit(5);
        } elseif (!$commentViewAll) {
            if ($reverseOrder) {
                $select->limit(5);
            } else {
                $select->limit(5, $commentCount - 5);
            }
        }

        $this->_comments = $table->fetchAll($select);

        foreach ($this->_comments as $comment) {
            $comment->body = Zend_Registry::get('Zend_View')->getHelper('getActionContent')
                ->updateActionContent($comment, $comment->body);
        }

        return $this->_comments;
    }

    public function getCommentsLikes($comments, $viewer)
    {
        if (empty($comments)) {
            return array();
        }

        $firstComment = $comments[0];
        if (!is_object($firstComment) ||
            !method_exists($firstComment, 'likes')) {
            return array();
        }

        $likes = $firstComment->likes();
        $table = $likes->getReceiver();

        $ids = array();

        foreach ($comments as $c) {
            $ids[] = $c->comment_id;
        }

        $select = $table
            ->select()
            ->from($table, 'resource_id')
            ->where('resource_id IN (?)', $ids)
            ->where('poster_type = ?', $viewer->getType())
            ->where('poster_id = ?', $viewer->getIdentity());

        if ($table instanceof Core_Model_DbTable_Likes) {
            $select->where('resource_type = ?', $firstComment->getType());
        }

        $isLiked = array();

        $rs = $table->fetchAll($select);

        foreach ($rs as $r) {
            $isLiked[$r->resource_id] = true;
        }

        return $isLiked;
    }

    public function comments()
    {
        $commentable = $this->getCommentable();
        switch ($commentable) {
            // Comments linked to action item
            default: case 0: case 1:
            return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('comments', 'activity'));
            break;

            // Comments linked to subject
            case 2:
                return $this->getSubject()->comments();
                break;

            // Comments linked to object
            case 3:
                return $this->getObject()->comments();
                break;

            // Comments linked to the first attachment
            case 4:
                $attachments = $this->getAttachments();
                if (!isset($attachments[0])) {
                    // We could just link them to the action item instead
                    throw new Activity_Model_Exception('No attachment to link comments to');
                }
                return $attachments[0]->item->comments();
                break;
        }

        throw new Activity_Model_Exception('Comment handler undefined');
    }

    public function likes()
    {
        $commentable = $this->getCommentable();
        switch ($commentable) {
            // Comments linked to action item
            default: case 0: case 1:
            return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('likes', 'activity'));
            break;

            // Comments linked to subject
            case 2:
                return $this->getSubject()->likes();
                break;

            // Comments linked to object
            case 3:
                return $this->getObject()->likes();
                break;

            // Comments linked to the first attachment
            case 4:
                $attachments = $this->getAttachments();
                if (!isset($attachments[0])) {
                    // We could just link them to the action item instead
                    throw new Activity_Model_Exception('No attachment to link comments to');
                }
                return $attachments[0]->item->likes();
                break;
        }

        throw new Activity_Model_Exception('Likes handler undefined');
    }

    public function deleteItem()
    {
        // delete comments that are not linked items
        if ($this->getCommentable() <= 1) {
            Engine_Api::_()->getDbtable('comments', 'activity')->delete(array(
                'resource_id = ?' => $this->action_id,
            ));

            // delete all "likes"
            Engine_Api::_()->getDbtable('likes', 'activity')->delete(array(
                'resource_id = ?' => $this->action_id,
            ));
            $this->_likes = null;
        }

        // lastly, delete item
        $this->delete();
    }

    public function getCommentable()
    {
        $commentable = (int) $this->getTypeInfo()->commentable;
        if ($commentable !== 4) {
            return $commentable;
        }
        $attachment = $this->getFirstAttachment();
        if (!($attachment && $attachment->item instanceof Core_Model_Item_Abstract)
            || !method_exists($attachment->item, 'comments')
            || !method_exists($attachment->item, 'likes')) {
            $commentable = 1;
        }

        return $commentable;
    }

    public function getCommentableItem()
    {
        $commentable = $this->getCommentable();

        // Comments linked to the first attachment
        if ($commentable === 4) {
            return $this->getFirstAttachment()->item;
        }

        return $this->getObject();
    }

    public function canEdit()
    {
        $editable = (int) $this->getTypeInfo()->editable;
        if (!$editable) {
            return;
        }
        $viewer = Engine_Api::_()->user()->getViewer();
        if (!$viewer->getIdentity()) {
            return false;
        }

        $maxEditTime = Engine_Api::_()->authorization()->getPermission($viewer->level_id, 'activity', 'edit_time');
        $isEditable = true;
        if ($maxEditTime) {
            $relDate = new Zend_Date($this->getTimeValue());
            $relDate->add((int) $maxEditTime, Zend_Date::MINUTE);
            $isEditable = $relDate->sub(Zend_Date::now())->toValue() > 0;
        }

        if (!$isEditable) {
            return false;
        }

        $activityModerate = Engine_Api::_()->getDbtable('permissions', 'authorization')
            ->getAllowed('user', $viewer->level_id, 'activity');
        if ($activityModerate) {
            return true;
        }

        return 'user' == $this->subject_type && $viewer->getIdentity() == $this->subject_id;
    }

    protected function _delete()
    {
        // Delete stream stuff
        Engine_Api::_()->getDbtable('stream', 'activity')->delete(array(
            'action_id = ?' => $this->action_id,
        ));

        // Delete attachments
        Engine_Api::_()->getDbtable('attachments', 'activity')->delete(array(
            'action_id = ?' => $this->action_id,
        ));

        parent::_delete();
    }

    public function getShareableItem()
    {
        $shareable = (int) $this->getTypeInfo()->shareable;
        if (!$shareable) {
            return;
        }
        if ($shareable === 2) {
            return $this->getSubject();
        }
        if ($shareable === 3) {
            return $this->getObject();
        }
        if ($shareable === 4) {
            return $this;
        }

        if ($shareable === 1 && $this->attachment_count === 1 && $this->getFirstAttachment()) {
            return $this->getFirstAttachment()->item;
        }
    }

    public function tags()
    {
      return new Engine_ProxyObject($this, Engine_Api::_()->getDbtable('tags', 'core'));
    }
}
