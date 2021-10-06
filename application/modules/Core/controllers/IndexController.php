<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_IndexController extends Core_Controller_Action_Standard {

  public function indexAction()
  {
    if( Engine_Api::_()->user()->getViewer()->getIdentity() )
    {
        return $this->_helper->redirector->gotoRoute(array('action' => 'home'), 'user_general', true);
    }

    // check public settings
    if( !Engine_Api::_()->getApi('settings', 'core')->core_general_portal &&
        !$this->_helper->requireUser()->isValid() ) {
        return;
    }

    // Render
    $this->_helper->content
        ->setNoRender()
        ->setEnabled()
    ;
  }
  
  public function inboxAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('messages_conversation')->getInboxPaginator($viewer);
    $paginator->setCurrentPageNumber($this->_getParam('page'));
    $paginator->setItemCountPerPage(10);
    $this->view->unread = Engine_Api::_()->messages()->getUnreadMessageCount($viewer);
  }
  
  public function deleteMessageAction() {

    $message_id = $this->getRequest()->getParam('id');
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();

    $db = Engine_Api::_()->getDbtable('messages', 'messages')->getAdapter();
    $db->beginTransaction();
    try {
      $recipients = Engine_Api::_()->getItem('messages_conversation', $message_id)->getRecipientsInfo();
      foreach ($recipients as $r) {
        if ($viewer_id == $r->user_id) {
          $this->view->deleted_conversation_ids[] = $r->conversation_id;
          $r->inbox_deleted = true;
          $r->outbox_deleted = true;
          $r->save();
        }
      }
      $db->commit();
    } catch (Exception $e) {
      $db->rollback();
      throw $e;
    }
  }
  
  public function markAllReadMessagesAction() {
    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    Engine_Api::_()->getDbtable('recipients', 'messages')->update(array('inbox_read' => 1), array('`user_id` = ?' => $viewer_id));
  }
  
  public function donotsellinfoAction() {
  
    $donotsellinfo = $this->_getParam('donotsellinfo', 0);
    $donotsellinfo = (int)($donotsellinfo === 'true');
    $viewer = Engine_Api::_()->user()->getViewer();
    $viewer->donotsellinfo = $donotsellinfo;
    $viewer->save();
    
    if($viewer->donotsellinfo == 1) {
      echo json_encode(array('status' => 'true', 'error' => ''));die;
    } else {
      echo json_encode(array('status' => 'false', 'error' => ''));die;
    }
  }
  
  public function showadmincontentAction() {
  
    $showcontent = $this->_getParam('showcontent', 0);
    $value = $this->_getParam('value');
    $showcontent = (int)($showcontent === 'true');
    
    $contentval = Engine_Api::_()->getApi('settings', 'core')->setSetting('core.newsupdates', $showcontent);
    echo json_encode(array('status' => 'false', 'error' => '', 'value' => 1));die;
  }
}
