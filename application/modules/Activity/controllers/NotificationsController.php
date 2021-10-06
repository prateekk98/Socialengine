<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: NotificationsController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_NotificationsController extends Core_Controller_Action_Standard
{

  public function init()
  {
    //$this->_helper->requireUser();
  }
  
  public function removeNotificationAction() {
    $notification_id = $this->_getParam('notification_id', null);
    $notification = Engine_Api::_()->getItem('activity_notification', $notification_id);

    try {
      $notification->delete();
      echo Zend_Json::encode(array('status' => 1));exit();
    } catch( Exception $e ) {
      echo 0;die;
    }
  }
  
  public function deleteNotificationAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    $this->view->form = $form = new Activity_Form_Delete();
    
    $notification = Engine_Api::_()->getItem('activity_notification', $this->_getParam('notification_id'));
    
    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
        return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
    }
        
    // Process
    $table = Engine_Api::_()->getItemTable('activity_notification');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $notification->delete();
      $db->commit();
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your notification entry has been deleted.');
      return $this->_forward('success' ,'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => Array($this->view->message)
      ));
    } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
    }
  }
  
  public function deleteNotificationsAction() {
  
    $viewer = Engine_Api::_()->user()->getViewer();
    
    // In smoothbox
    $this->_helper->layout->setLayout('default-simple');
    $this->view->form = $form = new Activity_Form_DeleteNotification();

    // If not post or form not valid, return
    if( !$this->getRequest()->isPost() ) {
        return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
        return;
    }
        
    // Process
    $table = Engine_Api::_()->getItemTable('activity_notification');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      $dbQuery = Zend_Db_Table_Abstract::getDefaultAdapter();
      $dbQuery->query('DELETE FROM `engine4_activity_notifications` WHERE `engine4_activity_notifications`.`user_id` = "'.$viewer->getIdentity().'";');
      $db->commit();
      $this->view->status = true;
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your notification entry has been deleted.');
      return $this->_forward('success' ,'utility', 'core', array(
          'smoothboxClose' => 10,
          'parentRefresh'=> 10,
          'messages' => Array($this->view->message)
      ));
    } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
    }
  }

  public function indexAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();

    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationsPaginator($viewer);
    $this->view->requests = Engine_Api::_()->getDbtable('notifications', 'activity')->getRequestsPaginator($viewer);
    $notifications->setCurrentPageNumber($this->_getParam('page'));

    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);

    $this->view->hasunread = false;

    // Now mark them all as read
    $ids = array();
    foreach( $notifications as $notification ) {
      $ids[] = $notification->notification_id;
    }
    //Engine_Api::_()->getDbtable('notifications', 'activity')->markNotificationsAsRead($viewer, $ids);
  }

  public function hideAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    Engine_Api::_()->getDbtable('notifications', 'activity')->markNotificationsAsRead($viewer);
    echo 1;die;
  }

  public function markreadAction()
  {
    $request = Zend_Controller_Front::getInstance()->getRequest();

    $action_id = $request->getParam('actionid', 0);

    $viewer = Engine_Api::_()->user()->getViewer();
    $notificationsTable = Engine_Api::_()->getDbtable('notifications', 'activity');
    $db = $notificationsTable->getAdapter();
    $db->beginTransaction();

    try {
      $notification = Engine_Api::_()->getItem('activity_notification', $action_id);
      if( $notification ) {
        $notification->read = 1;
        $notification->save();
      }
      // Commit
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
    
    if ($this->_helper->contextSwitch->getCurrentContext()  != 'json') {
      $this->_helper->viewRenderer->setNoRender();
    }
  }

  public function updateAction()
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      $this->view->notificationCount = $notificationCount = (int) Engine_Api::_()->getDbtable('notifications', 'activity')->hasNotifications($viewer);
    }

    $request = Zend_Controller_Front::getInstance()->getRequest();
    $this->view->notificationOnly = $request->getParam('notificationOnly', false);

    // @todo locale()->tonumber
    // array('%s update', '%s updates', $this->notificationCount), $this->locale()->toNumber($this->notificationCount));
    $this->view->text = $this->view->translate(array('%s Update', '%s Updates', $notificationCount), $notificationCount);
  }

  public function pulldownAction()
  {
    $page = $this->_getParam('page');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->notifications = $notifications = Engine_Api::_()->getDbtable('notifications', 'activity')->getNotificationsPaginator($viewer);
    $notifications->setCurrentPageNumber($page);

    if( $notifications->getCurrentItemCount() <= 0 || $page > $notifications->getCurrentPageNumber() ) {
      $this->_helper->viewRenderer->setNoRender(true);
      return;
    }

    // Force rendering now
    $this->_helper->viewRenderer->postDispatch();
    $this->_helper->viewRenderer->setNoRender(true);
  }

}
