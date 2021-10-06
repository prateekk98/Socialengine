<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Actions.php 10250 2014-06-02 13:51:20Z lucas $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Model_DbTable_Actions extends Engine_Db_Table
{
  protected $_rowClass = 'Activity_Model_Action';

  protected $_serializedColumns = array('params');

  protected $_actionTypes;

  public function addActivity(Core_Model_Item_Abstract $subject, Core_Model_Item_Abstract $object,
          $type, $body = null, array $params = null)
  {
    // Disabled or missing type
    $typeInfo = $this->getActionType($type);
    if( !$typeInfo || !$typeInfo->enabled )
    {
      return;
    }

    // User disabled publishing of this type
    $actionSettingsTable = Engine_Api::_()->getDbtable('actionSettings', 'activity');
    if( !$actionSettingsTable->checkEnabledAction($subject, $type) ) {
      return;
    }

    $privacy = isset($params['privacy']) ? $params['privacy'] : null;
    if ($privacy) {
      unset($params['privacy']);
    }
      $bodyEmojis = explode(' ', $body);
      foreach($bodyEmojis as $bodyEmoji) {
          $emojisCode = Engine_Text_Emoji::encode($bodyEmoji);
          $body = str_replace($bodyEmoji,$emojisCode,$body);
      }
    // Create action
    $action = $this->createRow();
    $action->setFromArray(array(
      'type' => $type,
      'subject_type' => $subject->getType(),
      'subject_id' => $subject->getIdentity(),
      'object_type' => $object->getType(),
      'object_id' => $object->getIdentity(),
      'body' => (string) $body,
      'privacy' => $privacy,
      'params' => (array) $params,
      'date' => date('Y-m-d H:i:s')
    ));
    $action->save();

    // Add bindings
    $this->addActivityBindings($action, $type, $subject, $object);

    // We want to update the subject
    if( isset($subject->modified_date) )
    {
      $subject->modified_date = date('Y-m-d H:i:s');
      $subject->save();
    }

    return $action;
  }

  public function removeActivities(Core_Model_Item_Abstract $subject, Core_Model_Item_Abstract $object, $type = null)
  {
    $select = $this->select()
        ->where('`subject_id` = ?', $subject->getIdentity())
        ->where('`object_id` = ?', $object->getIdentity())
        ->where('`subject_type` = ?', $subject->getType())
        ->where('`object_type` = ?', $object->getType());

    if( $type ) {
      $select->where('`type` = ?', $type);
    }

    foreach( $this->fetchAll($select) as $action ) {
      $action->deleteItem();
    }
  }

  public function getActivity(User_Model_User $user, array $params = array(), $about = null)
  {
    // Get actions
    if( $about instanceof Core_Model_Item_Abstract ) {
      $actions = $this->getActionsAbout($about, $user, $params);
    } else {
      $actions = $this->getActivityActions($user, $params);
    }

    // No visible actions
    if( empty($actions) )
    {
      return null;
    }

    // Process ids
    $ids = array();
    foreach( $actions as $data )
    {
      $ids[] = $data['action_id'];
    }
    $ids = array_unique($ids);

    // Finally get activity
    return $this->fetchAll(
      $this->select()
        ->where('action_id IN('.join(',', $ids).')')
        ->order('action_id DESC')
    );
  }

  public function getActivityActions(User_Model_User $user, array $params = array())
  {
    $actionId = $params['action_id'];
    $minId = $params['min_id'];
    $maxId = $params['max_id'];
    $limit = $params['limit'];

    // Prepare main query
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);
    // For Hashtag Search
    $hashtag = '';
    if (isset($params['hashtag'])) {
      $hastTagActionIds = $this->getHashTagActionsIds($params['hashtag']);
      if (empty($hastTagActionIds)) {
        return;
      }
    }

    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $mainActionTypes = array();

    // Filter out types set as not displayable
    foreach( $masterActionTypes as $type ) {
      if( $type->displayable & 4 ) {
        $mainActionTypes[] = $type->type;
      }
    }

    // Filter types based on user request
    if( isset($showTypes) && is_array($showTypes) && !empty($showTypes) ) {
      $mainActionTypes = array_intersect($mainActionTypes, $showTypes);
    } else if( isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes) ) {
      $mainActionTypes = array_diff($mainActionTypes, $hideTypes);
    }

    // Nothing to show
    if( empty($mainActionTypes) ) {
      return null;
    }
    // Show everything
    else if( count($mainActionTypes) == count($masterActionTypes) ) {
      $mainActionTypes = true;
    }
    // Build where clause
    else {
      $mainActionTypes = "'" . join("', '", $mainActionTypes) . "'";
    }

    $viewer = Engine_Api::_()->user()->getViewer();

    // For Admin & Moderators show all feed
    if( $viewer && $viewer->isAdmin() ) {
      $select = $streamTable->select()
        ->distinct()
        ->from($streamTable->info('name'), 'action_id')
        ;

      if( !empty($hastTagActionIds) ) {
        $select->where('action_id IN (?)', $hastTagActionIds);
      }

      // Add action_id/max_id/min_id
      if( $actionId !== null && $actionId != 0 ) {
        $select->where('action_id = ?', $actionId);
      } else {
        if( $minId !== null && $minId != 0 ) {
          $select->where('action_id >= ?', $minId);
        } elseif( $maxId !== null && $maxId != 0 ) {
          $select->where('action_id <= ?', $maxId);
        }
      }

      if( $mainActionTypes !== true ) {
        $select->where('type IN(' . $mainActionTypes . ')');
      }

      // Add order/limit
      $select
        ->order('action_id DESC')
        ->limit($limit);

      return $db->fetchAll($select);
    }

    // Prepare sub queries
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
      'for' => $user,
    ));
    $responses = (array) $event->getResponses();

    if( empty($responses) ) {
      return null;
    }

    foreach( $responses as $response )
    {
      if( empty($response) ) continue;

      $select = $streamTable->select()
        ->from($streamTable->info('name'), 'action_id')
        ->where('target_type = ?', $response['type'])
        ;

      if( empty($response['data']) ) {
        // Simple
        $select->where('target_id = ?', 0);
      } else if( is_scalar($response['data']) || count($response['data']) === 1 ) {
        // Single
        if( is_array($response['data']) ) {
          list($response['data']) = $response['data'];
        }
        $select->where('target_id = ?', $response['data']);
      } else if( is_array($response['data']) ) {
        // Array
        $select->where('target_id IN(?)', (array) $response['data']);
      } else {
        // Unknown
        continue;
      }

      if( !empty($hastTagActionIds) ) {
        $select->where('action_id IN (?)', $hastTagActionIds);
      }

      // Add action_id/max_id/min_id
      if( null !== $actionId && $actionId != 0) {
        $select->where('action_id = ?', $actionId);
      } else {
        if( $minId !== null && $minId != 0 ) {
          $select->where('action_id >= ?', $minId);
        } elseif( $maxId !== null && $maxId != 0 ) {
          $select->where('action_id <= ?', $maxId);
        }
      }

      if( $mainActionTypes !== true ) {
        $select->where('type IN(' . $mainActionTypes . ')');
      }

      // Add order/limit
      $select
        ->order('action_id DESC')
        ->limit($limit);

      // Add to main query
      $union->union(array('('.$select->__toString().')')); // (string) not work before PHP 5.2.0
    }

    // Finish main query
    $union
      ->order('action_id DESC')
      ->limit($limit);

    // Get actions
    return $db->fetchAll($union);
  }

  public function getActionsAbout(Core_Model_Item_Abstract $about, User_Model_User $user, array $params = array())
  {
    $actionId = $params['action_id'];
    $minId = $params['min_id'];
    $maxId = $params['max_id'];
    $limit = $params['limit'];

    // Prepare main query
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $db = $streamTable->getAdapter();
    $union = new Zend_Db_Select($db);

    // Prepare action types
    $masterActionTypes = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionTypes();
    $subjectActionTypes = array();
    $objectActionTypes = array();

    // Filter types based on displayable
    foreach( $masterActionTypes as $type ) {
      if( $type->displayable & 1 ) {
        $subjectActionTypes[] = $type->type;
      }
      if( $type->displayable & 2 ) {
        $objectActionTypes[] = $type->type;
      }
    }

    // Filter types based on user request
    if( isset($showTypes) && is_array($showTypes) && !empty($showTypes) ) {
      $subjectActionTypes = array_intersect($subjectActionTypes, $showTypes);
      $objectActionTypes = array_intersect($objectActionTypes, $showTypes);
    } else if( isset($hideTypes) && is_array($hideTypes) && !empty($hideTypes) ) {
      $subjectActionTypes = array_diff($subjectActionTypes, $hideTypes);
      $objectActionTypes = array_diff($objectActionTypes, $hideTypes);
    }

    // Nothing to show
    if( empty($subjectActionTypes) && empty($objectActionTypes) ) {
      return null;
    }

    if( empty($subjectActionTypes) ) {
      $subjectActionTypes = null;
    } else if( count($subjectActionTypes) == count($masterActionTypes) ) {
      $subjectActionTypes = true;
    } else {
      $subjectActionTypes = "'" . join("', '", $subjectActionTypes) . "'";
    }

    if( empty($objectActionTypes) ) {
      $objectActionTypes = null;
    } else if( count($objectActionTypes) == count($masterActionTypes) ) {
      $objectActionTypes = true;
    } else {
      $objectActionTypes = "'" . join("', '", $objectActionTypes) . "'";
    }

    $viewer = Engine_Api::_()->user()->getViewer();

    // For Admin & Moderators show all feed
    if( $viewer && $viewer->isAdmin() ) {
      $select = $streamTable->select()
        ->distinct()
        ->from($streamTable->info('name'), 'action_id')
        ;

      // Add actionId/maxId/minId
      if( $actionId !== null && $actionId != 0 ) {
        $select->where('action_id = ?', $actionId);
      } else {
        if( $minId !== null && $minId != 0 ) {
          $select->where('action_id >= ?', $minId);
        } elseif( $maxId !== null && $maxId != 0 ) {
          $select->where('action_id <= ?', $maxId);
        }
      }

      // Add subject to main query
      $selectSubject = clone $select;
      if( $subjectActionTypes !== null ) {
        if( $subjectActionTypes !== true ) {
          $selectSubject->where('type IN('.$subjectActionTypes.')');
        }
        $selectSubject
          ->where('subject_type = ?', $about->getType())
          ->where('subject_id = ?', $about->getIdentity());
        $union->union(array('('.$selectSubject->__toString().')')); // (string) not work before PHP 5.2.0
      }

      // Add object to main query
      $selectObject = clone $select;
      if( $objectActionTypes !== null ) {
        if( $objectActionTypes !== true ) {
          $selectObject->where('type IN('.$objectActionTypes.')');
        }
        $selectObject
          ->where('object_type = ?', $about->getType())
          ->where('object_id = ?', $about->getIdentity());
        $union->union(array('('.$selectObject->__toString().')')); // (string) not work before PHP 5.2.0
      }

      // Add order/limit
      $union
        ->order('action_id DESC')
        ->limit($limit);

      return $db->fetchAll($union);
    }

    // Prepare sub queries
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('getActivity', array(
      'for' => $user,
      'about' => $about,
    ));
    $responses = (array) $event->getResponses();

    if( empty($responses) ) {
      return null;
    }

    foreach( $responses as $response )
    {
      if( empty($response) ) continue;

      // Target info
      $select = $streamTable->select()
        ->from($streamTable->info('name'), 'action_id')
        ->where('target_type = ?', $response['type'])
        ;

      if( empty($response['data']) ) {
        // Simple
        $select->where('target_id = ?', 0);
      } else if( is_scalar($response['data']) || count($response['data']) === 1 ) {
        // Single
        if( is_array($response['data']) ) {
          list($response['data']) = $response['data'];
        }
        $select->where('target_id = ?', $response['data']);
      } else if( is_array($response['data']) ) {
        // Array
        $select->where('target_id IN(?)', (array) $response['data']);
      } else {
        // Unknown
        continue;
      }

      // Add actionId/maxId/minId
      if( $actionId !== null && $actionId != 0 ) {
        $select->where('action_id = ?', $actionId);
      } else {
        if( $minId !== null && $minId != 0 ) {
          $select->where('action_id >= ?', $minId);
        } elseif( $maxId !== null && $maxId != 0 ) {
          $select->where('action_id <= ?', $maxId);
        }
      }

      // Add order/limit
      $select
        ->order('action_id DESC')
        ->limit($limit);


      // Add subject to main query
      $selectSubject = clone $select;
      if( $subjectActionTypes !== null ) {
        if( $subjectActionTypes !== true ) {
          $selectSubject->where('type IN('.$subjectActionTypes.')');
        }
        $selectSubject
          ->where('subject_type = ?', $about->getType())
          ->where('subject_id = ?', $about->getIdentity());
        $union->union(array('('.$selectSubject->__toString().')')); // (string) not work before PHP 5.2.0
      }

      // Add object to main query
      $selectObject = clone $select;
      if( $objectActionTypes !== null ) {
        if( $objectActionTypes !== true ) {
          $selectObject->where('type IN('.$objectActionTypes.')');
        }
        $selectObject
          ->where('object_type = ?', $about->getType())
          ->where('object_id = ?', $about->getIdentity());
        $union->union(array('('.$selectObject->__toString().')')); // (string) not work before PHP 5.2.0
      }
    }

    // Finish main query
    $union
      ->order('action_id DESC')
      ->limit($limit);

    // Get actions
    return $db->fetchAll($union);
  }

  public function attachActivity($action, Core_Model_Item_Abstract $attachment, $mode = 1)
  {
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');

    if( is_numeric($action) )
    {
      $action = $this->fetchRow($this->select()->where('action_id = ?', $action)->limit(1));
    }

    if( !($action instanceof Activity_Model_Action) )
    {
      $eInfo = ( is_object($action) ? get_class($action) : $action );
      throw new Activity_Model_Exception(sprintf('Invalid action passed to attachActivity: %s', $eInfo));
    }

    $attachmentRow = $attachmentTable->createRow();
    $attachmentRow->action_id = $action->action_id;
    $attachmentRow->type = $attachment->getType();
    $attachmentRow->id = $attachment->getIdentity();
    $attachmentRow->mode = (int) $mode;
    $attachmentRow->save();

    $action->attachment_count++;
    $action->save();

    return $this;
  }

  public function detachFromActivity(Core_Model_Item_Abstract $attachment)
  {
    $attachmentsTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentsTable->select()
        ->where('`type` = ?', $attachment->getType())
        ->where('`id` = ?', $attachment->getIdentity())
        ;

    foreach( $attachmentsTable->fetchAll($select) as $row ) {
      $this->update(array(
        'attachment_count' => new Zend_Db_Expr('attachment_count - 1'),
      ), array(
        'action_id = ?' => $row->action_id,
      ));
      $row->delete();
    }

    return $this;
  }



  // Actions

  public function getActionById($action_id)
  {
    return $this->find($action_id)->current();
  }

  public function getActionsByObject(Core_Model_Item_Abstract $object)
  {
    $select = $this->select()->where('object_type = ?', $object->getType())
      ->where('object_id = ?', $object->getIdentity());
    return $this->fetchAll($select);
  }

  public function getActionsBySubject(Core_Model_Item_Abstract $subject)
  {
    $select = $this->select()
      ->where('subject_type = ?', $subject->getType())
      ->where('subject_id = ?', $subject->getIdentity())
      ;

    return $this->fetchAll($select);
  }

  public function getActionsByAttachment(Core_Model_Item_Abstract $attachment)
  {
    // Get all action ids from attachments
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $select = $attachmentTable->select()
      ->where('type = ?', $attachment->getType())
      ->where('id = ?', $attachment->getIdentity())
      ;

    $actions = array();
    foreach( $attachmentTable->fetchAll($select) as $attachmentRow )
    {
      $actions[] = $attachmentRow->action_id;
    }

    // Get all actions
    $select = $this->select()
      ->where('action_id IN(\''.join("','", $ids).'\')')
      ;

    return $this->fetchAll($select);
  }



  // Utility

  /**
   * Add an action-privacy binding
   *
   * @param int $action_id
   * @param string $type
   * @param Core_Model_Item_Abstract $subject
   * @param Core_Model_Item_Abstract $object
   * @return int The insert id
   */
  public function addActivityBindings($action)
  {
    // Get privacy bindings
    $privacy = $action->privacy;
    $event = Engine_Hooks_Dispatcher::getInstance()->callEvent('addActivity', array(
      'subject' => $action->getSubject(),
      'object' => $action->getObject(),
      'type' => $action->type,
      'content' => $privacy,
    ));

    $notInclude = false;
    if( !empty($privacy) && !in_array($privacy, array('everyone', 'networks', 'friends')) &&
        !Engine_Api::_()->activity()->isNetworkBasePrivacy($privacy)
    ) {
      $notInclude = true;
    }

    // Add privacy bindings
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    foreach( (array) $event->getResponses() as $response )
    {
      if( isset($response['target']) )
      {
        $target_type = $response['target'];
        $target_id = 0;
      }

      else if( isset($response['type']) && isset($response['identity']) )
      {
        $target_type = $response['type'];
        $target_id = $response['identity'];
      }

      else
      {
        continue;
      }

      if( Engine_Api::_()->activity()->isNetworkBasePrivacy($privacy) &&
        !in_array($target_type, array('network', 'owner', 'parent'))
      ) {
        continue;
      }

      if( $notInclude && !in_array($target_type, array('owner', 'parent')) ) {
        continue;
      }

      $streamTable->insert(array(
        'action_id' => $action->action_id,
        'type' => $action->type,
        'target_type' => (string) $target_type,
        'target_id' => (int) $target_id,
        'subject_type' => $action->subject_type,
        'subject_id' => $action->subject_id,
        'object_type' => $action->object_type,
        'object_id' => $action->object_id,
      ));
    }
    return $this;
  }

  public function clearActivityBindings($action)
  {
    $streamTable = Engine_Api::_()->getDbtable('stream', 'activity');
    $streamTable->delete(array(
      'action_id = ?' => $action->getIdentity(),
    ));
  }

  public function resetActivityBindings($action)
  {
    if ($action->getObject()) {
      $this->clearActivityBindings($action);
      $this->addActivityBindings($action);
    }
    return $this;
  }



  // Types

  /**
   * Gets action type meta info
   *
   * @param string $type
   * @return Engine_Db_Row
   */
  public function getActionType($type)
  {
    return $this->getActionTypes()->getRowMatching('type', $type);
  }

  /**
   * Gets all action type meta info
   *
   * @param string|null $type
   * @return Engine_Db_Rowset
   */
  public function getActionTypes()
  {
    if( null === $this->_actionTypes )
    {
      $table = Engine_Api::_()->getDbtable('actionTypes', 'activity');
      $this->_actionTypes = $table->fetchAll();
    }

    return $this->_actionTypes;
  }



  // Utility

  protected function _getInfo(array $params)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $args = array(
      'limit' => $settings->getSetting('activity.length', 20),
      'action_id' => null,
      'max_id' => null,
      'min_id' => null,
      'showTypes' => null,
      'hideTypes' => null,
    );

    $newParams = array();
    foreach( $args as $arg => $default ) {
      if( !empty($params[$arg]) ) {
        $newParams[$arg] = $params[$arg];
      } else {
        $newParams[$arg] = $default;
      }
    }

    return $newParams;
  }

  private function getHashTagActionsIds($search)
  {
    $hashtagIds = $actionIds = array();
    $tagTable = Engine_Api::_()->getDbtable('tags', 'core');
    $tagmapTable = Engine_Api::_()->getDbtable('TagMaps', 'core');
    $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
    $tagTableRow = $tagTable->fetchRow(
    $tagTable->select()
      ->where('text = ?', $search)
    );
    if( $tagTableRow ) {
      $hashtagIds[] = $tagTableRow->tag_id;
    }
    if (empty($search)) {
      $hashtagIds = $tagTable->select()->from($tagTable->info('name'), 'tag_id')->query()->fetchAll();
    }

    foreach( $hashtagIds as $hashtagId ) {
      $rowsets = $tagmapTable->fetchAll($tagmapTable->select()->where('tag_id = ?', $hashtagId));

      foreach( $rowsets as $row ) {
        if ($row->resource_type == 'activity_action') {
          $actionIds[] = $row->resource_id;
          continue;
        }
        if ($row->resource_type == 'activity_comment') {
            try {
                $item = Engine_Api::_()->getItem($row->resource_type, $row->resource_id);
                $item = $item ? $item->getParent() : null;
            }catch(Exception $e){
                $item = null;
            }
          if(!empty($item)) {
            $actionIds[] = $item->getIdentity();
          }
          continue;
        }
        if ($row->resource_type == 'core_comment') {
          $item = Engine_Api::_()->getItem($row->resource_type, $row->resource_id);
          $row = !empty($item) ? $item : $row;
        }

        $select = $attachmentTable->select()
          ->where('type = ? ', $row->resource_type)
          ->where('id = ? ', $row->resource_id);
        $attachments = $attachmentTable->fetchAll($select);
        foreach ($attachments as $attachment) {
          $action = Engine_Api::_()->getItem('activity_action', $attachment->action_id);
          if (!$action) {
            continue;
          }

          $actionIds[] = $attachment->action_id;
        }
      }
    }

    return $actionIds;
  }
  
  function deleteActivityFeed($params = array()) {
    $select = $this->select()
            ->where('type =?', $params['type'])
            ->where('subject_id =?', $params['subject_id'])
            ->where('object_type =?', $params['object_type'])
            ->where('object_id =?', $params['object_id']);
    $actionObject = $this->fetchRow($select);
    if($actionObject)
      $actionObject->delete();
  }
}
