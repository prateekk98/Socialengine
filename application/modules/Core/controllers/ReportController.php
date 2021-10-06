<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ReportController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_ReportController extends Core_Controller_Action_Standard
{
  public function init()
  {
    $this->_helper->requireUser();
    $this->_helper->requireSubject();
  }

  public function createAction()
  {
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject();

    $this->view->form = $form = new Core_Form_Report();
    $form->populate($this->_getAllParams());

    if( !$this->getRequest()->isPost() )
    {
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) )
    {
      return;
    }

    // Process
    $table = Engine_Api::_()->getItemTable('core_report');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try
    {
      $viewer = Engine_Api::_()->user()->getViewer();
      
      $report = $table->createRow();
      $report->setFromArray(array_merge($form->getValues(), array(
        'subject_type' => $subject->getType(),
        'subject_id' => $subject->getIdentity(),
        'user_id' => $viewer->getIdentity(),
      )));
      $report->save();

      // Increment report count
      Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.reports');
      $adminLink = 'http://' . $_SERVER['HTTP_HOST'] .
            Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'core', 'controller' => 'report'), 'admin_default', true);

      $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
      $allAdmins = Engine_Api::_()->getItemTable('user')->getAllAdmin();
      $authTable = Engine_Api::_()->authorization()->getAdapter('levels');
      $adminSideLink = '<a href="'.$adminLink.'" >'.$this->view->translate("site").'</a>';

      foreach ($allAdmins as $admin) {
        if($viewer->isSelf($admin)){
          continue;
        }
        if($authTable->getAllowed('user', $admin, 'abuseNotifi')){
          $useProfileLink = '<a href="'.$viewer->getHref().'" >'.$this->view->translate("User").'</a>';
          $senderName = '<a href="'.$viewer->getHref().'" >'.$viewer->getTitle().'</a>';

          $notifyApi->addNotification($admin, $viewer, $admin, 'abuse_report',array('userprofilelink'=>$useProfileLink,'adminsidelink'=>$adminSideLink));
        }
        if($authTable->getAllowed('user', $admin, 'abuseEmail')){
          Engine_Api::_()->getApi('mail', 'core')->sendSystem($admin,
                    "abuse_report",array("admin_link"=>$adminLink,'sender_name'=>$senderName));
        }
      }
      $db->commit();
    }

    catch( Exception $e )
    {
      $db->rollBack();
      throw $e;
    }

    // Close smoothbox
    $currentContext = $this->_helper->contextSwitch->getCurrentContext();
    if( null === $currentContext )
    {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }
    else if( 'smoothbox' === $currentContext )
    {
      return $this->_forward('success', 'utility', 'core', array(
        'messages' => $this->view->translate('Your report has been submitted.'),
        'smoothboxClose' => true,
        'parentRefresh' => false,
      ));
    }
  }
}
