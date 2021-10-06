<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminLevelController.php 9836 2012-11-29 00:51:00Z pamela $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Authorization_AdminLevelController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('authorization_admin_main', array(), 'authorization_admin_main_manage');

    $this->view->formFilter = $formFilter = new Authorization_Form_Admin_Level_Filter();
    $page = $this->_getParam('page', 1);

    $table = Engine_Api::_()->getDbtable('levels', 'authorization');
    $select = $table->select();

    if( $formFilter->isValid($this->_getAllParams()) ) {
      $values = $formFilter->getValues();

      $select = $table->select()
       ->order( !empty($values['orderby']) ? $values['orderby'].' '.$values['orderby_direction'] : 'level_id DESC' );
      
      if( $values['orderby'] && $values['orderby_direction'] != 'ASC') {
        $this->view->orderby = $values['orderby'];
      }
    }

    $this->view->paginator = $paginator = Zend_Paginator::factory($select);
    $paginator->setCurrentPageNumber( $page );


    // Sanity check levels?
    $defaultLevelDuplicates = $table->select()
      ->from($table)
      ->where('flag = ?', 'default')
      ->query()
      ->fetchAll();

    // Check for multiple default levels?
    if( count($defaultLevelDuplicates) != 1 ) {
      // Remove where type != 'user'
      foreach( array_keys($defaultLevelDuplicates) as $key ) {
        $level = $defaultLevelDuplicates[$key];
        if( $level['type'] != 'user' ) {
          $table->update(array(
            'flag' => '',
          ), array(
            'level_id = ?' => $level['level_id'],
            'flag = ?' => 'default',
          ));
          unset($defaultLevelDuplicates[$key]);
        }
        if( count($defaultLevelDuplicates) <= 0 ) {
          $newDefaultLevelId = $table->select()
            ->from($table, 'level_id')
            ->where('type = ?', 'user')
            ->limit(1)
            ->query()
            ->fetchColumn();
        } else {
          $newDefaultLevelId = array_shift($defaultLevelDuplicates);
          $newDefaultLevelId = $newDefaultLevelId['level_id'];
        }
        if( $newDefaultLevelId ) {
          $table->update(array(
            'flag' => 'default',
          ), array(
            'level_id = ?' => $newDefaultLevelId,
          ));
        }
      }
      return $this->_helper->redirector->gotoRoute(array());
    }
  }

  public function createAction()
  {
    $this->view->form = $form = new Authorization_Form_Admin_Level_Create();

    if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) )
    {

      $table = Engine_Api::_()->getDbtable('levels', 'authorization');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        $values = $form->getValues();
        
        $level = $table->createRow();
        $level->setFromArray($values);
        $level->save();

        //@todo duplicate the settings of given parent value
        // does this go into the authorization_permission table?
        // $values['parent'];
        // select permission for the parent level
        $permissionTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
        $select = $permissionTable->select()->where('level_id = ?', $values['parent']);
        $parent_permissions = $table->fetchAll($select);


        // create permissions
        foreach( $parent_permissions as $parent )
        {
          $permissions = $permissionTable->createRow();
          $permissions->setFromArray($parent->toArray());
          $permissions->level_id = $level->level_id;
          $permissions->save();
        }

        // Commit
        $db->commit();

        // Redirect
        return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
        //$this->_helper->redirector->gotoRoute(array());
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }

    }
  }

  public function deleteAction()
  {
    $this->view->form = $form = new Authorization_Form_Admin_Level_Delete();
    $id = $this->_getParam('id', null);

    // check to make sure the level is not default
    $this->view->level = $level = Engine_Api::_()->getItem('authorization_level', $id);

    if($level->flag){
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }

    if( $id )
    {
      $form->level_id->setValue($id);
    }

    if( $this->getRequest()->isPost() )
    {
      $table = Engine_Api::_()->getDbtable('levels', 'authorization');
      $db = $table->getAdapter();
      $db->beginTransaction();

      try
      {
        // remove all permissions associated with this levle
        $level->removeAllPermissions();

        // reallocate users to default level
        $level->reassignMembers();

        // delete level
        $level->delete();

        // commit
        $db->commit();

        // Delete mapping
        Engine_Api::_()->authorization()->mappingGC();

        return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
      }

      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
    }
  }

  public function editAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('authorization_admin_main', array(), 'authorization_admin_main_level');
    
    // Get level id
    if( null !== ($id = $this->_getParam('id')) ) {
      $this->view->level = $level = Engine_Api::_()->getItem('authorization_level', $id);
      $this->view->level_id = $id;
    } else {
      $this->view->level = $level = Engine_Api::_()->getItemTable('authorization_level')->getDefaultLevel();
      $this->view->level_id = $id = $level->level_id;
    }
   
    $this->view->form = $form = new Authorization_Form_Admin_Level_Edit(array(
      'public' => ( in_array($level->type, array('public')) ),
      'moderator' => ( in_array($level->type, array('admin', 'moderator')) ),
      'admin' => ( in_array($level->type, array('admin')) ),
    ));
    $this->view->permissionTable = $permissionsTable = Engine_Api::_()->getDbtable('permissions', 'authorization');
    
    // Populate
    $form->populate($level->toArray());
    $form->populate($permissionsTable->getAllowed('user', $id, array_keys($form->getValues())));

    $messagesAuth = $permissionsTable->getAllowed('messages', $id, 'auth');
    $messagesEditor = $permissionsTable->getAllowed('messages', $id, 'editor');
    $form->populate(array(
      'messages_auth' => $messagesAuth,
      'messages_editor' => $messagesEditor,
    ));

    $form->populate(array(
      'activity_edit_time' => $permissionsTable->getAllowed('activity', $id, 'edit_time'),
    ));

    $form->getElement('title')->setValue($level->title);

    if (isset($form->coverphoto_dummy)) {
      $href = Engine_Api::_()->user()->getViewer()->getHref() . '?uploadDefaultCover=1&level_id='.$id;
      $description = sprintf(
        "%1sClick here%2s to upload and set default user cover photo. "
          . "(Note: This photo will be displayed until members upload a cover photo.)",
        "<a href='$href' target='_blank'>", "</a>"
      );
      $form->coverphoto_dummy->setDescription($description);
    }

    if(Engine_Api::_()->getApi("settings", "core")->getSetting("usercoverphoto.preview.level.id.$id")) {
      $image = Engine_Api::_()->storage()->get(
        Engine_Api::_()->getApi("settings", "core")->getSetting("usercoverphoto.preview.level.id.$id"),
        'thumb.cover'
      )->map();
      $description = sprintf("%1sPreview Default Cover Photo%2s",
        "<a onclick='showPreview();'>",
        "</a><div ' id='show_default_preview' class='is_hidden'>"
          . "<img src='$image' style='max-height:600px;max-width:600px;'></div>"
      );
      $form->addElement('dummy', 'coverphoto_preview', array(
        'description' => $description,
      ));

      $form->coverphoto_preview->addDecorator(
        'Description',
        ['placement' => 'PREPEND', 'class' => 'description', 'escape' => false]
      );
    }

    // Check method/valid
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }


    // Process
    $values = $form->getValues();
    $level->title = $values['title'];
    $level->description = $values['description'];
    $level->save();

    // get messages
    $messageAuth = $values['messages_auth'];
    unset($values['messages_auth']);

    $messageEditor = $values['messages_editor'];
    unset($values['mesages_editor']);

    $activityMaxEditTime = $values['activity_edit_time'];
    unset($values['activity_edit_time']);

    // coverphoto work
    unset($values['coverphoto_dummy']);
    unset($values['coverphoto_preview']);

    // Form elements with NonBoolean values
    $nonBooleanSettings = $form->nonBooleanFields();

    // set level specific settings for profile, activity and html comments
    $permissionsTable->setAllowed('user', $level->level_id, $values, '', $nonBooleanSettings);

    $nonBooleanSettings = array('auth');
    $permissionsTable->setAllowed('messages', $level->level_id, array(
      'create' => ( $messageAuth == 'everyone' || $messageAuth == 'friends' ),
      'auth' => $messageAuth,
    ), '', $nonBooleanSettings);

    $nonBooleanSettings = array('editor');
    $permissionsTable->setAllowed('messages', $level->level_id, array(
      'editor' => $messageEditor,
    ), '', $nonBooleanSettings);

    $nonBooleanSettings = array('edit_time');
    $permissionsTable->setAllowed('activity', $level->level_id, array(
      'edit_time' => $activityMaxEditTime,
    ), '', $nonBooleanSettings);
    // show changes saved message
    $form->addNotice('Your changes have been saved.');
  }

  public function deleteselectedAction()
  {
    // $this->view->form = $form = new Announcement_Form_Admin_Edit();
    $this->view->ids = $ids = $this->_getParam('ids', null);
    $confirm = $this->_getParam('confirm', false);
    $this->view->count = count(explode(",", $ids));

    // $announcement = Engine_Api::_()->getItem('announcement', $id);

    // Save values
    if( $this->getRequest()->isPost() && $confirm == true )
    {
      $idsString = explode(",", $ids);

      foreach ($idsString as $id){
        $level = Engine_Api::_()->getItem('authorization_level', $id);

        // make sure the ID is not part of the ones that cannot be deleted
        if( !$level->flag ) {
          // remove all permissions associated with this levle
          $level->removeAllPermissions();

          // reallocate users to default level
          $level->reassignMembers();

          // delete level
          $level->delete();
        }
      }

      //$announcement->delete();
      return $this->_helper->redirector->gotoRoute(array('action' => 'index'));
    }
  }

  public function setDefaultAction()
  {
    if( !$this->getRequest()->isPost() ) {
      return;
    }

    // Get level
    if( !($id = $this->_getParam('level_id')) ||
        !($level = Engine_Api::_()->getItem('authorization_level', $id)) ) {
      return;
    }
    $this->view->level = $level;

    $table = Engine_Api::_()->getItemTable('authorization_level');
    $db = $table->getAdapter();
    $db->beginTransaction();

    try {
      // Remove default
      $table->update(array(
        'flag' => '',
      ), array(
        'flag = ?' => 'default',
      ));
      
      // set the current item to default
      $level->flag = 'default';
      $level->save();
      
      $db->commit();
    } catch( Exception $e ) {
      $db->rollBack();
      throw $e;
    }
  }

  public function manageProfileTypeMappingAction()
  {
    $this->view->navigation = $navigation = Engine_Api::_()->getApi('menus', 'core')
      ->getNavigation('authorization_admin_main', array(), 'authorization_admin_main_level_mapprofiletype');
    $mapProfileTable = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization');
    $this->view->paginator = Zend_Paginator::factory($mapProfileTable->select());
    Engine_Api::_()->authorization()->mappingGC();
  }

  public function mapProfileTypeAction()
  {
    $mappedProfile = Engine_Api::_()->getItem('mapProfileTypeLevel', $this->_getParam('id'));
    $profileTypes = Engine_Api::_()->getDbtable('options', 'authorization')->getAllProfileTypes();

    $mapProfileTable = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization');
    $mappedProfileTypeIds = $mapProfileTable->getMappedProfileTypeIds();
    if (!empty($profileTypeId = $this->_getParam('profileTypeId'))) {
      $mappedProfile = Engine_Api::_()->getItem('mapProfileTypeLevel', $mapProfileTable->getMappingId($profileTypeId));
    }

    if ((count($mappedProfileTypeIds) >= count($profileTypes)) && !$mappedProfile) {
      $this->view->message = Zend_Registry::get('Zend_Translate')->_('There are no un-mapped Profile Types left.');
      return;
    }

    $this->view->form = $form = new Authorization_Form_Admin_ProfileTypeLevelMap_Update();
    if ($mappedProfile) {
      $form->populate($mappedProfile->toArray());
    }

    if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    if (!$mappedProfile) {
      $mappedProfile = $mapProfileTable->createRow();
    }
    $mappedProfile->setFromArray($form->getValues());
    $mappedProfile->save();

    $this->_helper->redirector->gotoRoute(array(
      'action' => 'update-member-level',
      'controller' => 'level',
      'profile_type_id' => $mappedProfile->profile_type_id,
      'level_id' => $mappedProfile->member_level_id
    ), 'admin_default', false);
  }

  public function deleteMappingAction()
  {
    if (!$mapprofile = Engine_Api::_()->getItem(
        'mapProfileTypeLevel',
        $this->_getParam('id', $this->_getParam('mapprofiletypelevel_id', null))
    )) {
      return;
    }

    $this->view->form = $form = new Authorization_Form_Admin_ProfileTypeLevelMap_Delete();
    $form->level_id->setValue($mapprofile->member_level_id);

    if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
      return;
    }

    $profileTypeUsers = Engine_Api::_()->getDbtable('values', 'authorization')->getUsersFromMapping($mapprofile->mapprofiletypelevel_id);

    $userTable = Engine_Api::_()->getItemTable('user');
    foreach ($profileTypeUsers as $profileTypeUser) {
      $userTable->update(array( 'level_id' => $_POST['level_id'] ), array(
        'user_id = ?' => $profileTypeUser['item_id'],
        'level_id != ?' => 1
      ));
    }

    try {
      $mapprofile->delete();
      Engine_Api::_()->core()->clearSubject();
    } catch (Exception $ex) {
      throw $ex;
    }

    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => true,
      'parentRefresh' => true,
      'messages' => Array(Zend_Registry::get('Zend_Translate')->_('The mapping has been successfully deleted.'))
    ));
  }

  public function deleteSelectedMappingAction()
  {
    $this->view->ids = $ids = $this->_getParam('actions', $this->_getParam('ids', array()));
    $confirm = $this->_getParam('confirm', false);

    if( is_string($ids) ) {
      $ids = explode(',', $ids);
    }
    $this->view->idsString = $idsString =  join(',', $ids);
    $this->view->count = count($ids);
    $this->view->member_levels = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc();
    $profileTypesArray = array();

    if (!empty($idsString)) {
      $mapProfileTable = Engine_Api::_()->getItemTable('mapProfileTypeLevel');
      $mapProfileTableName = $mapProfileTable->info('name');
      $select = $mapProfileTable->select()
        ->from($mapProfileTableName, array('profile_type_id', 'member_level_id'))
        ->where("mapprofiletypelevel_id IN ($idsString)");
      $profileTypesArray = $select->query()->fetchAll();
    }
    $this->view->profile_types = $profileTypesArray;

    if( $this->getRequest()->isPost() && $confirm == true )
    {
      $db = $mapProfileTable->getAdapter();
      $db->beginTransaction();
      try
      {
        $ids_strings = implode(",", $ids);
        $userTable = Engine_Api::_()->getItemTable('user');
        $profileTypeUsers = Engine_Api::_()->getDbtable('values', 'authorization')->getUsersFromMapping($ids_strings);
        foreach ($profileTypeUsers as $profileTypeUser) {
          $selected_level_id = $this->_getParam('level_id_' . $profileTypeUser['value']);
          if (!empty($selected_level_id)) {
            $userTable->update(array(
              'level_id' => $selected_level_id,
            ), array(
              'user_id = ?' => $profileTypeUser['item_id'],
              'level_id != ?' => 1
            ));
          }
        }
        foreach( $ids as $id ) {
          $mapprofile = Engine_Api::_()->getItem('mapProfileTypeLevel', $id);
          $mapprofile->delete();
        }
        $db->commit();
      }
      catch( Exception $e )
      {
        $db->rollBack();
        throw $e;
      }
      return $this->_helper->redirector->gotoRoute(array('action' => 'manage-profile-type-mapping', 'id' => null));
    }
  }

  public function updateMemberLevelAction()
  {

    $this->_helper->layout->setLayout('default-simple');
    if (!$this->getRequest()->isPost()) {
      $this->view->profile_type_id = $profileTypeId =  $this->_getParam('profile_type_id', null);
      $this->view->member_level_id = $levelId =  $this->_getParam('level_id', null);
      $this->view->user_infos  = $this->getUsersInfo($profileTypeId);
      $this->view->profile_type_label = Engine_Api::_()->getItem('option', $profileTypeId)->label;
      $this->view->member_level = Engine_Api::_()->getItem('authorization_level', $levelId)->getTitle();
    }

    if ($this->getRequest()->isPost()) {
      if(!empty($_POST['userids'])){
        $profileTypesUsers = explode("," , $_POST['userids']);
        if (!empty($profileTypesUsers)) {
          $userTable = Engine_Api::_()->getItemTable('user');
          foreach ($profileTypesUsers as $value) {
            $userTable->update(array(
              'level_id' => $_POST['level_id'],
            ), array(
              'user_id = ?' => $value,
              'level_id != ?' => 1,
            ));
          }
        }
      }
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => true,
        'parentRefresh'=> true,
        'messages' => Array(Zend_Registry::get('Zend_Translate')->_('The mapping has been created successfully.'))
      ));
    }
  }

  private function getUsersInfo($profileTypeId)
  {
    $userInfos = '';
    $profileTypesUsers = Engine_Api::_()->getDbtable('values', 'authorization')->getProfileTypeUsers($profileTypeId);

    $userIds = array_column($profileTypesUsers, 'item_id');
    if (!empty($userIds)) {
      $userIdsArray = implode(",", $userIds);
      $userTable = Engine_Api::_()->getItemTable('user');
      $fieldselect = $userTable->select()
        ->setIntegrityCheck(false)
        ->from($userTable->info('name'), array('COUNT(*) AS count'))
        ->where("level_id = ?", 1)
        ->where("user_id IN ($userIdsArray)");
      $mp_levels = $userTable->fetchAll($fieldselect)->toArray();
      $userInfos= array('userids'=> $userIdsArray,
        'total_users' => count ($userIds),
        'total_superadmin_users' => $mp_levels[0]['count']
      );
    }

    return $userInfos;
  }
}
