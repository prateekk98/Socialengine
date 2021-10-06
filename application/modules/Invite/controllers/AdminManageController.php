<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminManageController.php 9919 2013-02-16 00:46:04Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Invite_AdminManageController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->formFilter = $formFilter = new Invite_Form_Admin_Manage_Filter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbtable('invites', 'invite');
    $select = $table->select();

    // Process form
    $values = array();
    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();
    }

    foreach( $values as $key => $value ) {
      if( null === $value ) {
        unset($values[$key]);
      }
    }

    $values = array_merge(array(
      'order' => 'id',
      'order_direction' => 'DESC',
    ), $values);

    $this->view->assign($values);

    // Set up select info
    $select->where('new_user_id =?', 0)->order(( !empty($values['order']) ? $values['order'] : 'id' ) . ' ' . ( !empty($values['order_direction']) ? $values['order_direction'] : 'DESC' ));

    if( !empty($values['recipient']) ) {
      $select->where('recipient LIKE ?', '%' . $values['recipient'] . '%');
    }
    if( !empty($values['id']) ) {
      $select->where('id = ?', (int) $values['id']);
    }

    // Filter out junk
    $valuesCopy = array_filter($values);

    // Make paginator
    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $this->view->paginator = $paginator->setCurrentPageNumber( $page );
    $this->view->formValues = $valuesCopy;

    $this->view->hideEmails = _ENGINE_ADMIN_NEUTER;
    $this->view->viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();

    $this->view->openUser = (bool) ( $this->_getParam('open') && $paginator->getTotalItemCount() == 1 );
  }

  public function multiModifyAction()
  {
    if( $this->getRequest()->isPost() ) {
      $values = $this->getRequest()->getPost();
      
      foreach ($values as $key=>$value) {
        if( $key == 'modify_' . $value ) {
          $invite = Engine_Api::_()->getItem('invite', (int) $value);
          if( $values['submit_button'] == 'delete' ) {
            $invite->delete();
          }
        }
      }
    }
    return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
  }

  public function deleteAction() {
  
    $id = $this->_getParam('id', null);
    $invite = Engine_Api::_()->getItem('invite', (int) $id);
    $this->view->form = $form = new Invite_Form_Admin_Manage_Delete();
    if( $this->getRequest()->isPost() ) {
      $db = Engine_Api::_()->getDbtable('invites', 'invite')->getAdapter();
      $db->beginTransaction();
      try {
        $invite->delete();
        $db->commit();
      } catch( Exception $e ) {
        $db->rollBack();
        throw $e;
      }
      return $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh' => true,
        'format'=> 'smoothbox',
        'messages' => array('This invite has been successfully deleted.')
      ));
    }
  }
}
