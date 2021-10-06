<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminThemesController.php 10165 2014-04-14 15:37:08Z lucas $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_AdminBannersController extends Core_Controller_Action_Admin
{

  public function indexAction()
  {
    $table = Engine_Api::_()->getDbTable('banners', 'core');
    $this->view->params = $params = array(
      'limit' => $this->_getParam('limit', 10),
      'page' => $this->_getParam('page', 1),
    );
    $this->view->paginator = $table->getBannersPaginator($params);
  }

  public function createAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Banners_Create();
    // Check stuff
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    // Save
    $values = $form->getValues();
    $title = $values['title'];
    unset($values['title']);
    $body = $values['body'];
    unset($values['body']);
    $photo = $values['photo'];
    unset($values['photo']);
    $table = Engine_Api::_()->getDbTable('banners', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $banner = $table->createRow();
      $banner->title = $title;
      $banner->body = $body;
      $banner->name = 'custom_temp';
      $banner->params = $values;
      $banner->module = 'core';
      $banner->custom = 1;
      $banner->save();
      $banner->name = 'custom_' . sprintf('%d', $banner->banner_id);
      $banner->save();
      // Set photo
      if( !empty($photo) ) {
        $banner->setPhoto($form->photo);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }
    // redirect to manage page for now
    $this->_helper->redirector->gotoRoute(array('module' => 'core', 'controller' => 'banners'), 'admin_default', true);
  }

  public function editAction()
  {
    $this->view->banner_id = $bannerId = $this->_getParam('id');
    $this->view->banner = $banner = Engine_Api::_()->getDbtable('banners', 'core')->getBanner($bannerId);
    if( !$banner ) {
      throw new Core_Model_Exception('missing banner');
    }

    $this->view->form = $form = new Core_Form_Admin_Banners_Edit();

    // Make safe
    $bannerData = $banner->toArray();
    if( is_array($bannerData['params']) ) {
      $bannerData = array_merge($bannerData, $bannerData['params']);
    }

    if( !$banner->custom ) {
      $form->removeElement('uri');
    }

    // Check stuff
    if( !$this->getRequest()->isPost() ) {
      $form->populate($bannerData);
      return;
    }

    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }
    // Save
    $values = $form->getValues();
    $title = $values['title'];
    unset($values['title']);
    $body = $values['body'];
    unset($values['body']);
    $photo = $values['photo'];
    unset($values['photo']);
    $table = Engine_Api::_()->getDbTable('banners', 'core');
    $db = $table->getAdapter();
    $db->beginTransaction();
    try {
      $banner->title = $title;
      $banner->body = $body;
      if( $banner->custom ) {
        $banner->params = $values;
      } else {
        $banner->params = array_merge($banner->params, array('label' => $values['label']));
      }

      $banner->save();
      // Set photo
      if( !empty($photo) ) {
        $banner->setPhoto($form->photo);
      }
      $db->commit();
    } catch( Exception $e ) {
      $db->rollback();
      throw $e;
    }
    // redirect to manage page for now
    $this->_helper->redirector->gotoRoute(array('module' => 'core', 'controller' => 'banners'), 'admin_default', true);
  }

  public function deleteAction()
  {
    $this->view->banner_id = $bannerId = $this->_getParam('id');
    $banner = Engine_Api::_()->getDbtable('banners', 'core')->getBanner($bannerId);

    if( !$banner || !$banner->custom ) {
      throw new Core_Model_Exception('missing menu item');
    }

    // Get form
    $this->view->form = $form = new Core_Form_Admin_Banners_Delete();

    // Check stuff
    if( !$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    $banner->delete();
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'format' => 'smoothbox',
      'messages' => array(Zend_Registry::get('Zend_Translate')->_("Banner Deleted"))
    ));
  }

  public function previewAction()
  {
    $this->view->banner_id = $bannerId = $this->_getParam('id');
    $banner = Engine_Api::_()->getDbtable('banners', 'core')->getBanner($bannerId);
    if( !$banner ) {
      throw new Core_Model_Exception('missing banner');
    }
    $this->_helper->layout->setLayout('default-simple');
    $this->view->banner = $banner;
  }

}
