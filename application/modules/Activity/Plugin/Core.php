<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Plugin_Core
{
  public function onActivityActionCreateAfter($event) {
    if (!in_array('hashtags', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'))) {
      return;
    }

    $payload = $event->getPayload();
    if ($payload->body) {
      $hashtags = Engine_Api::_()->activity()->getHashTags($payload->body);
      $payload->tags()->addTagMaps(Engine_Api::_()->user()->getViewer(), $hashtags[0]);
    }
  }

  public function onActivityActionDeleteBefore($event) {
    if (!in_array('hashtags', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'))) {
      return;
    }

    $payload = $event->getPayload();
    $hashtags = Engine_Api::_()->activity()->getHashTags($payload->body);
    $payload->tags()->removeTagMaps($hashtags[0]);
  }

  public function onActivityActionUpdateAfter($event) {
    if (!in_array('hashtags', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'))) {
      return;
    }

    $payload = $event->getPayload();
    $viewer = Engine_Api::_()->user()->getViewer();
    if (in_array('body', $payload->getModifiedFieldsName())) {
      $hashtags = Engine_Api::_()->activity()->getHashTags($payload->body);
      $hashtags = !empty($hashtags[0]) ? $hashtags[0] : array();
      $cleanData = $payload->getCleanData();
      $oldBody = $cleanData['body'];
      $deleteHashTag = array();
      $oldHashtags = Engine_Api::_()->activity()->getHashTags($oldBody);
      if (!empty($oldHashtags[0])) {
        $deleteHashTag = array_diff($oldHashtags[0], $hashtags);
        $hashtags = array_diff($hashtags, $oldHashtags[0]);
      }
      $payload->tags()->removeTagMaps($deleteHashTag);
      $payload->tags()->addTagMaps($viewer, $hashtags);
    }

    if (!in_array('attachment_count', $payload->getModifiedFieldsName()) || $payload->attachment_count != 1) {
      return;
    }

    $attachment = $payload->getFirstAttachment();
    if (empty($attachment) || !($attachment->item instanceof Core_Model_Item_Abstract)) {
      return;
    }

    $object = $attachment->item;
    if (!($object instanceof Activity_Model_Action)) {
      return;
    }

    $content = $object->body;
    $hashtags = Engine_Api::_()->activity()->getHashTags($content);
    if (empty($hashtags) || empty($hashtags[0])) {
      return;
    }

    $payload->tags()->addTagMaps($viewer, $hashtags);
  }

  public function onItemDeleteBefore($event)
  {
    $item = $event->getPayload();

    $activityTable = Engine_Api::_()->getDbtable('actions', 'activity');

    $activitySelect = $activityTable->select()
      ->where("object_type = '".$item->getType()."' AND object_id = ".$item->getIdentity())
      ->orwhere("subject_type = '".$item->getType()."' AND subject_id = ".$item->getIdentity());

    foreach($activityTable->fetchAll($activitySelect) as $activityRow) {
      $activityRow->delete();
    }

    if( $item instanceof User_Model_User ) {
      Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array(
        'user_id = ?' => $item->getIdentity(),
      ));
    }

    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array(
      'subject_type = ?' => $item->getType(),
      'subject_id = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array(
      'object_type = ?' => $item->getType(),
      'object_id = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('stream', 'activity')->delete(array(
      'subject_type = ?' => $item->getType(),
      'subject_id = ?' => $item->getIdentity(),
    ));

    Engine_Api::_()->getDbtable('stream', 'activity')->delete(array(
      'object_type = ?' => $item->getType(),
      'object_id = ?' => $item->getIdentity(),
    ));

    // Delete all attachments and parent posts
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $attachmentSelect = $attachmentTable->select()
      ->where('type = ?', $item->getType())
      ->where('id = ?', $item->getIdentity())
      ;
      
    $attachmentActionIds = array();
    foreach( $attachmentTable->fetchAll($attachmentSelect) as $attachmentRow ) {
      $attachmentActionIds[] = $attachmentRow->action_id;
      try{
          $action = Engine_Api::_()->getItem('activity_action', $attachmentRow->action_id);
          if($action && $action->type == "share" && $action->attachment_count == 1){
              $action->delete();
          }
      }catch(Exception $e){}
    }

    if( !empty($attachmentActionIds) ) {
      $attachmentTable->delete('action_id IN('.join(',', $attachmentActionIds).')');
      Engine_Api::_()->getDbtable('stream', 'activity')->delete('action_id IN('.join(',', $attachmentActionIds).')');
    }

    if( $item->getType() == "user" ) {
      Engine_Api::_()->getDbtable('notificationSettings', 'activity')->delete(array(
        'user_id = ?' => $item->getIdentity(),
      ));
    }

  }

  public function getActivity($event)
  {
    // Detect viewer and subject
    $payload = $event->getPayload();
    $user = null;
    $subject = null;
    if( $payload instanceof User_Model_User ) {
      $user = $payload;
    } elseif( is_array($payload) ) {
      if( isset($payload['for']) && $payload['for'] instanceof User_Model_User ) {
        $user = $payload['for'];
      }
      if( isset($payload['about']) && $payload['about'] instanceof Core_Model_Item_Abstract ) {
        $subject = $payload['about'];
      }
    }
    if( null === $user ) {
      $viewer = Engine_Api::_()->user()->getViewer();
      if( $viewer->getIdentity() ) {
        $user = $viewer;
      }
    }
    if( null === $subject && Engine_Api::_()->core()->hasSubject() ) {
      $subject = Engine_Api::_()->core()->getSubject();
    }

    // Get feed settings
    $content = Engine_Api::_()->getApi('settings', 'core')
      ->getSetting('activity.content', 'everyone');

    // Owner
    if( $user ) {
      $event->addResponse(array(
        'type' => 'owner',
        'data' => $user->getIdentity()
      ));
    }

    // Parent
    if( $user ) {
      $event->addResponse(array(
        'type' => 'parent',
        'data' => $user->getIdentity()
      ));
    }

    // Members (friends)
    if( $user ) {
      $data = array();
      $data = $user->membership()->getMembershipsOfIds();

      if( !empty($data) ) {
        $event->addResponse(array(
          'type' => 'members',
          'data' => $data,
        ));
      }
    }

    // Network
    if( $user && ($subject || in_array($content, array('networks', 'everyone'))) ) {

      $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
      $ids = $networkTable->getMembershipsOfIds($user);

      foreach( $ids as $id ) {
        $event->addResponse(array(
          'type' => 'network',
          'data' => $id
        ));
      }
    }

    // Registered and Everyone
    if( $user && ($subject || $content == "everyone") ) {
      // Registered
      $event->addResponse(array(
        'type' => 'registered',
        'data' => 0
      ));

      // Everyone
      $event->addResponse(array(
        'type' => 'everyone',
        'data' => 0
      ));
    }
  }

  public function addActivity($event)
  {
    $payload = $event->getPayload();
    $subject = $payload['subject'];
    $object = $payload['object'];
    $content = isset($payload['content']) && !empty($payload['content']) ? $payload['content'] :
      Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');

    // Get subject owner
    $subjectOwner = null;
    if( $subject instanceof User_Model_User ) {
      $subjectOwner = $subject;
    } else {
      try {
        $subjectOwner = $subject->getOwner('user');
      } catch( Exception $e ) {
      }
    }

    // Get object parent
    $objectParent = null;
    if( $object instanceof User_Model_User ) {
      if($object->getType() == 'album_photo') {
        $objectParent = $object->getOwner();
      } else {
        $objectParent = $object;
      }
    } else {
      try {
        if($object->getType() == 'album_photo') {
          $objectParent = $object->getOwner();
          $object = $object->getParent();
        } else {
          $objectParent = $object->getParent();
        }
      } catch( Exception $e ) {
      }
    }

    // Owner
    if( $subjectOwner instanceof User_Model_User ) {
      $event->addResponse(array(
        'type' => 'owner',
        'identity' => $subjectOwner->getIdentity()
      ));
    }

    // Parent
    if( $objectParent instanceof User_Model_User ) {
      $event->addResponse(array(
        'type' => 'parent',
        'identity' => $objectParent->getIdentity()
      ));
    }

    // Network
    if( in_array($content, array('everyone', 'networks')) ) {
      if( $object instanceof User_Model_User
          && Engine_Api::_()->authorization()->context->isAllowed($object, 'network', 'view') ) {
        $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
        $ids = $networkTable->getMembershipsOfIds($object);
        $ids = array_unique($ids);
        foreach( $ids as $id ) {
          $event->addResponse(array(
            'type' => 'network',
            'identity' => $id,
          ));
        }
      } elseif( $objectParent instanceof User_Model_User
          && Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_network', 'view') ) {
        $networkTable = Engine_Api::_()->getDbtable('membership', 'network');
        $ids = $networkTable->getMembershipsOfIds($objectParent);
        $ids = array_unique($ids);
        foreach( $ids as $id ) {
          $event->addResponse(array(
            'type' => 'network',
            'identity' => $id,
          ));
        }
      }
    }
    if (!in_array($content, array('everyone', 'networks', 'friends', 'onlyme'))) {
      if (Engine_Api::_()->activity()->isNetworkBasePrivacy($content)) {
        $ids = Engine_Api::_()->activity()->getNetworkBasePrivacyIds($content);
        $ids = array_unique($ids);
        foreach ($ids as $id) {
          $event->addResponse(array(
            'type' => 'network',
            'identity' => $id,
          ));
        }
      }
    }

    // Members
    if( $object instanceof User_Model_User ) {
      if( Engine_Api::_()->authorization()->context->isAllowed($object, 'member', 'view') ) {
        $event->addResponse(array(
          'type' => 'members',
          'identity' => $object->getIdentity()
        ));
      }
    } elseif( $objectParent instanceof User_Model_User ) {
      // Note: technically we shouldn't do owner_member, however some things are using it
      if( Engine_Api::_()->authorization()->context->isAllowed($object, 'owner_member', 'view') ||
          Engine_Api::_()->authorization()->context->isAllowed($object, 'parent_member', 'view') ) {
        $event->addResponse(array(
          'type' => 'members',
          'identity' => $objectParent->getIdentity()
        ));
      }
    }

    // Registered
    if( $content == 'everyone' &&
        Engine_Api::_()->authorization()->context->isAllowed($object, 'registered', 'view') ) {
      $event->addResponse(array(
        'type' => 'registered',
        'identity' => 0
      ));
    }

    // Everyone
    if( $content == 'everyone' &&
        Engine_Api::_()->authorization()->context->isAllowed($object, 'everyone', 'view') ) {
      $event->addResponse(array(
        'type' => 'everyone',
        'identity' => 0
      ));
    }
  }

  public function onActivityCommentCreateAfter($event)
  {
    $payload = $event->getPayload();
    $commentedItem = $payload->getResource();
    $viewer = $payload->getPoster();
    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
    $owner = $commentedItem->getOwner();
    if( $owner->getType() != 'user' || $owner->getIdentity() == $viewer->getIdentity() ) {
      return;
    }
    $body = $payload['body'];
    $action = $activityApi->addActivity($viewer, $commentedItem, 'comment_' . $commentedItem->getType(), $body, array(
      'owner' => $commentedItem->getOwner('user')->getGuid(),
      'body' => $body
    ));
    if( $action ) {
      $activityApi->attachActivity($action, $commentedItem);
    }
  }

  public function onActivityCommentDeleteBefore($event)
  {
    $payload = $event->getPayload();
    $commentedItem = $payload->getResource();
    $poster = $payload->getPoster();
    $activityApi = Engine_Api::_()->getDbtable('actions', 'activity');
    $activityApi->removeActivities($poster, $commentedItem, 'comment_' . $commentedItem->getType());
  }

}
