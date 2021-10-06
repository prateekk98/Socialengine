<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10180 2014-04-28 21:02:01Z lucas $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @todo SignupController.php: integrate invite-only functionality (reject if invite code is bad)
 * @todo AdminController.php: add in stricter settings for admin level checking
 */
class Invite_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {
    // Render
    $this->_helper->content
        //->setNoRender()
        ->setEnabled()
        ;
    
    $settings = Engine_Api::_()->getApi('settings', 'core');

    // Check if admins only
    if( $settings->getSetting('user.signup.inviteonly') == 1 ) {
      if( !$this->_helper->requireAdmin()->isValid() ) {
        return;
      }
    }

    // Check for users only
    if( !$this->_helper->requireUser()->isValid() ) {
      return;
    }

    // Make form
    $this->view->form = $form = new Invite_Form_Invite();

    if( !$this->getRequest()->isPost() ) {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    
    // Process
    $values = $form->getValues();
    $this->view->recipients = $values['recipients'];
    $this->view->allInvites = Engine_Api::_()->getDbTable('invites', 'invite')->getAllInvites($values['recipients']);
    $recipients = explode(',', $values['recipients']);
    $recipients = array_map('trim', $recipients);

    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    if(count($recipients) == 1) {
      $this->view->canInvite = Engine_Api::_()->getDbTable('invites', 'invite')->canInvite($recipients);
    }
    
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $db = $inviteTable->getAdapter();
    $db->beginTransaction();
    
    try {
      $inviteTable->setDefaultAlreadyMembers([]);
      $emailsSent = $inviteTable->sendInvites($viewer, $values['recipients'], @$values['message'],$values['friendship'], '');
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      if( APPLICATION_ENV == 'development' ) {
        throw $e;
      }
    }

    $this->view->already_members = $inviteTable->getAlreadyMembers();
    
    $this->view->emails_sent = $emailsSent;
    
    return $this->render('sent');
  }
  
  public function resendinviteAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->invite_id = $invite_id = $this->_getParam('invite_id', null);
    $this->view->invite = $invite = Engine_Api::_()->getItem('invite', $invite_id);

    // Process
    $inviteTable = Engine_Api::_()->getDbtable('invites', 'invite');
    $db = $inviteTable->getAdapter();
    $db->beginTransaction();
    try {
        
      
    
      $emailsSent = $inviteTable->sendInvites($viewer, $invite->recipient, 'You are being invited to join our social network.', '', 'resend');
      $invite->delete();
      $db->commit();
      echo json_encode(array('status' => 'true', 'message' => 'Resend Invite Successfully.'));die;
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
  
  public function notifyadminAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->invite_id = $invite_id = $this->_getParam('invite_id', null);
    $this->view->invite = $invite = Engine_Api::_()->getItem('invite', $invite_id);

    // Process
    $table = $invite->getTable();
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
    
      $adminLink = 'http://' . $_SERVER['HTTP_HOST'] . Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'invite', 'controller' => 'manage'), 'admin_default', true);

      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      $allAdmins = Engine_Api::_()->getItemTable('user')->getAllAdmin();
      $adminSideLink = '<a href="'.$adminLink.'" >'.$this->view->translate("cancel").'</a>';

      foreach ($allAdmins as $admin) {
        if($viewer->isSelf($admin)){
          continue;
        }
        $useProfileLink = '<a href="'.$viewer->getHref().'" >'.$viewer->getTitle().'</a>';
        $notifyApi->addNotification($admin, $viewer, $admin, 'invite_notify_admin',array('userprofilelink'=>$useProfileLink,'adminsidelink'=>$adminSideLink,'recipientemail' => $invite->recipient));
      }
      $db->commit();
      echo json_encode(array('status' => 'true', 'message' => 'Notify Admin Successfully.'));die;
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }
}
