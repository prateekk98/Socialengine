<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10075 2013-07-30 21:51:18Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_IndexController extends Core_Controller_Action_Standard
{
  public function indexAction()
  {

  }

  public function homeAction()
  {
    // check public settings
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.portal', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }

    if( !Engine_Api::_()->user()->getViewer()->getIdentity() ) {
      return $this->_helper->redirector->gotoRoute(array(), 'default', true);
    }

    // Render
    $this->_helper->content
        ->setNoRender()
        ->setEnabled()
        ;
  }

  public function browseAction()
  {
    $require_check = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.browse', 1);
    if(!$require_check){
      if( !$this->_helper->requireUser()->isValid() ) return;
    }
    if( !$this->_executeSearch() ) {
      // throw new Exception('error');
    }

    $this->view->isAjaxSearch = $this->_getParam('ajax');

    if ($this->view->isAjaxSearch) {
      $this->renderScript('_browseUsers.tpl');
    }

    if( !$this->view->isAjaxSearch ) {
      // Render
      $this->_helper->content
          ->setEnabled()
          ;
    }
  }

  protected function _executeSearch()
  {
    // Check form
    $form = new User_Form_Search(array(
      'type' => 'user'
    ));

    if (!$form->isValid($this->_getAllParams())) {
      $this->view->error = true;
      $this->view->totalUsers = 0; 
      $this->view->userCount = 0; 
      $this->view->page = 1;
      return false;
    }

    $this->view->form = $form;

    // Get search params
    $page = (int)  $this->_getParam('page', 1);
    $ajax = (bool) $this->_getParam('ajax', false);
    $options = $form->getValues();
    
    // Process options
    $tmp = array();
    $originalOptions = $options;
    foreach ($options as $k => $v) {
      if (null == $v || '' == $v || (is_array($v) && count(array_filter($v)) == 0)) {
        continue;
      } elseif (false !== strpos($k, '_field_')) {
        list($null, $field) = explode('_field_', $k);
        $tmp['field_' . $field] = $v;
      } elseif (false !== strpos($k, '_alias_')) {
        list($null, $alias) = explode('_alias_', $k);
        $tmp[$alias] = $v;
      } else {
        $tmp[$k] = $v;
      }
    }
    $options = $tmp;

    // Get table info
    $table = Engine_Api::_()->getItemTable('user');
    $userTableName = $table->info('name');

    $searchTable = Engine_Api::_()->fields()->getTable('user', 'search');
    $searchTableName = $searchTable->info('name');

    //extract($options); // displayname
    $profile_type = @$options['profile_type'];
    $displayname = @$options['displayname'];
    if (!empty($options['extra'])) {
      extract($options['extra']); // is_online, has_photo, submit
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $viewerId = $viewer->getIdentity();
    $excludedLevels = array(1, 2, 3);
    $allBlockedUsers = array();

    if( $viewerId ) {
      $blockTable = Engine_Api::_()->getDbtable('block', 'user');
      $blockedSelect = $blockTable->select()
        ->from('engine4_user_block', 'blocked_user_id')
        ->where('user_id = ?', $viewerId);
      $blockedUsers = $blockTable->fetchAll($blockedSelect)->toArray();

      foreach( $blockedUsers as $blockedUser ) {
        array_push($allBlockedUsers, $blockedUser['blocked_user_id']);
      }
      $this->view->blockedUserIds = $allBlockedUsers;

      if( !in_array($viewer->level_id, $excludedLevels) ) {
        $blockedBySelect = $blockTable->select()
          ->from('engine4_user_block', 'user_id')
          ->where('blocked_user_id = ?', $viewerId);
        $blockedByUsers = $blockTable->fetchAll($blockedBySelect)->toArray();

        foreach( $blockedByUsers as $blockedByUser ) {
          array_push($allBlockedUsers, $blockedByUser['user_id']);
        }
      } else {

        unset($allBlockedUsers);
      }
    }

    // Contruct query
    $select = $table->select()
      //->setIntegrityCheck(false)
      ->from($userTableName)
      ->joinLeft($searchTableName, "`{$searchTableName}`.`item_id` = `{$userTableName}`.`user_id`", null)
      //->group("{$userTableName}.user_id")
      ->where("{$userTableName}.search = ?", 1)
      ->where("{$userTableName}.enabled = ?", 1);

    if( !empty($allBlockedUsers) ) {
      $select->where("user_id NOT IN (?)", $allBlockedUsers);
    }
    $searchDefault = true;

    // Build the photo and is online part of query
    if (isset($has_photo) && !empty($has_photo)) {
      $select->where($userTableName.'.photo_id != ?', "0");
      $searchDefault = false;
    }

    if (isset($is_online) && !empty($is_online)) {
      $select
        ->joinRight("engine4_user_online", "engine4_user_online.user_id = `{$userTableName}`.user_id", null)
        ->group("engine4_user_online.user_id")
        ->where($userTableName.'.user_id != ?', "0");
      $searchDefault = false;
    }

    // Add displayname
    if (!empty($displayname)) {
      $select->where("(`{$userTableName}`.`username` LIKE ? || `{$userTableName}`.`displayname` LIKE ?)", "%{$displayname}%");
      $searchDefault = false;
    }

    // Build search part of query
    $searchParts = Engine_Api::_()->fields()->getSearchQuery('user', $options);
    foreach ($searchParts as $k => $v) {
      if (strpos($k, 'FIND_IN_SET') !== false) {
        $select->where("{$k}", $v);
        continue;
      }

      $select->where("`{$searchTableName}`.{$k}", $v);

      if (isset($v) && $v != "") {
        $searchDefault = false;
      }
    }

    $orderby = $this->getParam('orderby');
    $orderbyOptions = array('member_count', 'creation_date');

    if (!empty($orderby) && in_array($orderby, $orderbyOptions)) {
      $select->order($orderby . " DESC");
    } elseif ($searchDefault) {
      $select->order("{$userTableName}.lastlogin_date DESC");
    } else {
      $select->order("{$userTableName}.displayname ASC");
    }

    // Build paginator
    $paginator = Zend_Paginator::factory($select);
    $paginator->setItemCountPerPage(12);
    $paginator->setCurrentPageNumber($page);
    
    $this->view->page = $page;
    $this->view->ajax = $ajax;
    $this->view->users = $paginator;
    $this->view->totalUsers = $paginator->getTotalItemCount();
    $this->view->userCount = $paginator->getCurrentItemCount();
    $this->view->topLevelId = $form->getTopLevelId();
    $this->view->topLevelValue = $form->getTopLevelValue();
    $this->view->formValues = array_filter($originalOptions);

    return true;
  }
}
