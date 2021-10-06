<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_EditController extends Core_Controller_Action_User
{
    public function init()
    {
        if (!Engine_Api::_()->core()->hasSubject()) {
            // Can specifiy custom id
            $id = $this->_getParam('id', null);
            $subject = null;
            if (null === $id) {
                $subject = Engine_Api::_()->user()->getViewer();
                Engine_Api::_()->core()->setSubject($subject);
            } else {
                $subject = Engine_Api::_()->getItem('user', $id);
                Engine_Api::_()->core()->setSubject($subject);
            }
        }

        if (!empty($id)) {
            $params = array('id' => $id);
        } else {
            $params = array();
        }
        // Set up navigation
        $this->view->navigation = $navigation = Engine_Api::_()
            ->getApi('menus', 'core')
            ->getNavigation('user_edit', array('params' => $params));

        // Set up require's
        $this->_helper->requireUser();
        $this->_helper->requireSubject('user');
        $this->_helper->requireAuth()->setAuthParams(
            null,
            null,
            'edit'
        );
    }

    public function profileAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        // General form w/o profile type
        $profileTypesArray = [];
        $aliasedFields = $user->fields()->getFieldsObjectsByAlias();
        $changeUserProfileType = Engine_Api::_()->getDbtable('values', 'authorization')->changeUsersProfileType($user);
        $this->view->topLevelId = $topLevelId = 0;
        $this->view->topLevelValue = $topLevelValue = null;
        if (isset($aliasedFields['profile_type'])) {
          $aliasedFieldValue = $aliasedFields['profile_type']->getValue($user);
          $topLevelId = $aliasedFields['profile_type']->field_id;
          $topLevelValue = (is_object($aliasedFieldValue) ? $aliasedFieldValue->value : null);
          if (!$topLevelId || !$topLevelValue) {
            $topLevelId = null;
            $topLevelValue = null;
          }
          $this->view->topLevelId = $topLevelId;
          $this->view->topLevelValue = $topLevelValue;
        }

        if ($changeUserProfileType) {
          $profileTypesArray = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')
            ->getMappedProfileTypeIds($user->level_id);
          if (count($profileTypesArray) == 1) {
            $this->view->topLevelId = $topLevelId = 1;
            $this->view->topLevelValue = $topLevelValue = $profileTypesArray[0]['profile_type_id'];
          }
        }

        $privacyExemptFields = [$aliasedFields['first_name']['field_id'], $aliasedFields['last_name']['field_id']];
        $this->view->privacyExemptFields = json_encode($privacyExemptFields);

        // Get form
        $form = $this->view->form = new Fields_Form_Standard(array(
          'item' => Engine_Api::_()->core()->getSubject(),
          'topLevelId' => $topLevelId,
          'topLevelValue' => $topLevelValue,
          'hasPrivacy' => true,
          'privacyValues' => $this->getRequest()->getParam('privacy'),
        ));

        if (!empty($profileTypesArray) && count($profileTypesArray) == 1) {
          $form->addElement('Hidden', '0_0_1', array(
            'value' => $profileTypesArray[0]['profile_type_id']
          ));
        }

        if (empty($topLevelValue) && $changeUserProfileType) {
          $profileTypes = Engine_Api::_()->getDbtable('options', 'authorization')->getAllProfileTypes();
          $profileTypeOptions = array('' => '');
          foreach ($profileTypes as $profileType) {
            $showOption  = false;
            foreach($profileTypesArray as $value) {
              if ($profileType->option_id == $value['profile_type_id']) {
                $showOption = true;
              }
            }
            if ($showOption) {
              $profileTypeOptions[$profileType->option_id] = $profileType->label;
            }
          }
          $form->getElement('0_0_1')->setMultiOptions($profileTypeOptions);
        }

        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $form->saveValues();

            // Update display name
            $aliasValues = Engine_Api::_()->fields()->getFieldsValuesByAlias($user);
            $user->setDisplayName($aliasValues);
            //$user->modified_date = date('Y-m-d H:i:s');
            $user->save();

            // update networks
            Engine_Api::_()->network()->recalculate($user);

            $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
        }
    }


    public function photoAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        // Get form
        $this->view->form = $form = new User_Form_Edit_Photo();

        if (empty($user->photo_id)) {
            $form->removeElement('remove');
        }

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Uploading a new photo
        if ($form->Filedata->getValue() !== null) {
            $db = $user->getTable()->getAdapter();
            $db->beginTransaction();

            try {
                // if album not enable remove old photo.
                if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')){
                    // if user photo_id column is empty.
                    if(!empty($user['photo_id'])){
                        $file = Engine_Api::_()->getItem('storage_file', $user['photo_id']);
                        $getParentChilds = $file->getChildren($file->getIdentity());
                        foreach ($getParentChilds as $child) {
                            // remove child file.
                            $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $child['storage_path']);
                            // remove child directory.
                            $childPhotoDir = $this->getDirectoryPath($child['storage_path']);
                            $this->removeDir($childPhotoDir);
                            // remove child row from db.
                            $child->remove();
                        }
                        // remove parent file.
                        $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $file['storage_path']);
                        // remove directory.
                        $parentPhotoDir = $this->getDirectoryPath($file['storage_path']);
                        $this->removeDir($parentPhotoDir);
                        if ($file) {
                            // remove parent form db.
                            $file->remove();
                        }
                    }
                }
                $form->coordinates->setValue('');  //reset coordinates value
                $fileElement = $form->Filedata;

                $user->setPhoto($fileElement);

                $iMain = Engine_Api::_()->getItem('storage_file', $user->photo_id);

                // Insert activity
                $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'profile_photo_update',
                    '{item:$subject} added a new profile photo.');

                // Hooks to enable albums to work
                if ($action) {
                    $event = Engine_Hooks_Dispatcher::_()
                        ->callEvent('onUserPhotoUpload', array(
                            'user' => $user,
                            'file' => $iMain,
                        ));

                    $attachment = $event->getResponse();
                    if (!$attachment) {
                        $attachment = $iMain;
                    }

                    // We have to attach the user himself w/o album plugin
                    Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
                }

                $db->commit();
            } catch (Exception $e) {
                return $this->exceptionWrapper($e, $form, $db);
            }
        }

        // Resizing a photo
        elseif ($form->getValue('coordinates') !== '') {
            $storage = Engine_Api::_()->storage();

            $iProfile = $storage->get($user->photo_id, 'thumb.profile');
            if (!$iProfile) {
                return;   // don't do anything
            }
            $iSquare = $storage->get($user->photo_id, 'thumb.icon');

            // Read into tmp file
            $pName = $iProfile->getStorageService()->temporary($iProfile);
            $iName = dirname($pName) . '/nis_' . basename($pName);

            list($x, $y, $w, $h) = explode(':', $form->getValue('coordinates'));

            $image = Engine_Image::factory();
            $image->open($pName)
                ->resample($x+.1, $y+.1, $w-.1, $h-.1, 48, 48)
                ->write($iName)
                ->destroy();

            $iSquare->store($iName);

            $image = Engine_Image::factory();
            $image->open($pName)
                ->resample($x+.1, $y+.1, $w-.1, $h-.1, 440, 440)
                ->write($pName)
                ->destroy();
            $iProfile->store($pName);
            // Remove temp files
            @unlink($iName);
        } else {
            $storage = Engine_Api::_()->storage();

            $iProfile = $storage->get($user->photo_id, 'thumb.profile');
            if (!$iProfile) {
                return;   // don't do anything
            }

            $pName = $iProfile->getStorageService()->temporary($iProfile);
            $image = Engine_Image::factory();
            $image->open($pName);
            $profileImgRatio = $image->width / $image->height ;

            if ($profileImgRatio == 1) {
                return;
            }

            $size = min($image->height, $image->width);
            $x = ($image->width - $size) / 2;
            $y = ($image->height - $size) / 2;

            $image->resample($x, $y, $size, $size, 400, 400)
                ->write($pName)
                ->destroy();
            $iProfile->store($pName);

            @unlink($pName);
        }
    }

    protected function getDirectoryPath($storage_path){
        return APPLICATION_PATH . DIRECTORY_SEPARATOR . str_replace(basename($storage_path),"",$storage_path);
    }

    protected function removeDir($dirPath){
        if(@is_dir($dirPath)){
           @rmdir($dirPath);
        }
    }

    protected function unlinkFile($filePath){
        @unlink($filePath);
    }

    protected function whenRemove($user,$deleteType = null){
    if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')){
      if(!empty($user[$deleteType])){
        $file = Engine_Api::_()->getItem('storage_file', $user[$deleteType]);
        $getParentChilds = $file->getChildren($file->getIdentity());
        foreach ($getParentChilds as $child) {
          // remove child file.
          $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $child['storage_path']);
          // remove child directory.
          $childPhotoDir = $this->getDirectoryPath($child['storage_path']);
          $this->removeDir($childPhotoDir);
          // remove child row from db.
          $child->remove();
        }
        // remove parent file.
        $this->unlinkFile(APPLICATION_PATH . DIRECTORY_SEPARATOR . $file['storage_path']);
        // remove directory.
        $parentPhotoDir = $this->getDirectoryPath($file['storage_path']);
        $this->removeDir($parentPhotoDir);
        if ($file) {
          // remove parent form db.
          $file->remove();
        }
      }
    }
  }

    public function removePhotoAction()
    {
        // Get form
        $this->view->form = $form = new User_Form_Edit_RemovePhoto();

        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            return;
        }


        $user = Engine_Api::_()->core()->getSubject();
        $this->whenRemove($user,"photo_id");
        $user->photo_id = 0;
        $user->save();

        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.');

        $this->_forward('success', 'utility', 'core', array(
            'smoothboxClose' => true,
            'parentRefresh' => true,
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Your photo has been removed.'))
        ));
    }

    public function styleAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
        if (!$this->_helper->requireAuth()->setAuthParams('user', null, 'style')->isValid()) {
            return;
        }


        // Get form
        $this->view->form = $form = new User_Form_Edit_Style();

        // Get current row
        $table = Engine_Api::_()->getDbtable('styles', 'core');
        $select = $table->select()
            ->where('type = ?', $user->getType())
            ->where('id = ?', $user->getIdentity())
            ->limit();

        $row = $table->fetchRow($select);

        // Not posting, populate
        if (!$this->getRequest()->isPost()) {
            $form->populate(array(
                'style' => (null === $row ? '' : $row->style)
            ));
            return;
        }

        // Whoops, form was not valid
        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }


        // Cool! Process
        $style = $form->getValue('style');

        // Process
        $style = strip_tags($style);

        $forbiddenStuff = array(
            '-moz-binding',
            'expression',
            'javascript:',
            'behaviour:',
            'vbscript:',
            'mocha:',
            'livescript:',
        );

        $style = str_replace($forbiddenStuff, '', $style);

        // Save
        if (null == $row) {
            $row = $table->createRow();
            $row->type = $user->getType();
            $row->id = $user->getIdentity();
        }

        $row->style = $style;
        $row->save();

        $form->addNotice(Zend_Registry::get('Zend_Translate')->_('Your changes have been saved.'));
    }

    public function externalPhotoAction()
    {
        if (!$this->_helper->requireSubject()->isValid()) {
            return;
        }
        $user = Engine_Api::_()->core()->getSubject();

        // Get photo
        $photo = Engine_Api::_()->getItemByGuid($this->_getParam('photo'));
        if (!$photo || !($photo instanceof Core_Model_Item_Abstract) || empty($photo->photo_id)) {
            $this->_forward('requiresubject', 'error', 'core');
            return;
        }

        if (!$photo->authorization()->isAllowed(null, 'view')) {
            $this->_forward('requireauth', 'error', 'core');
            return;
        }


        // Make form
        $this->view->form = $form = new User_Form_Edit_ExternalPhoto();
        $this->view->photo = $photo;

        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process
        $db = $user->getTable()->getAdapter();
        $db->beginTransaction();

        try {
            // Get the owner of the photo
            $photoOwnerId = null;
            if (isset($photo->user_id)) {
                $photoOwnerId = $photo->user_id;
            } elseif (isset($photo->owner_id) && (!isset($photo->owner_type) || $photo->owner_type == 'user')) {
                $photoOwnerId = $photo->owner_id;
            }

            // if it is from your own profile album do not make copies of the image
            if ($photo instanceof Album_Model_Photo &&
                ($photoParent = $photo->getParent()) instanceof Album_Model_Album &&
                $photoParent->owner_id == $photoOwnerId &&
                $photoParent->type == 'profile') {

                // ensure thumb.icon and thumb.profile exist
                $newStorageFile = Engine_Api::_()->getItem('storage_file', $photo->file_id);
                $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
                if ($photo->file_id == $filesTable->lookupFile($photo->file_id, 'thumb.profile')) {
                    try {
                        $tmpFile = $newStorageFile->temporary();
                        $image = Engine_Image::factory();
                        $image->open($tmpFile)
                            ->resize(200, 400)
                            ->write($tmpFile)
                            ->destroy();
                        $iProfile = $filesTable->createFile($tmpFile, array(
                            'parent_type' => $user->getType(),
                            'parent_id' => $user->getIdentity(),
                            'user_id' => $user->getIdentity(),
                            'name' => basename($tmpFile),
                        ));
                        $newStorageFile->bridge($iProfile, 'thumb.profile');
                        @unlink($tmpFile);
                    } catch (Exception $e) {
                        echo $e;
                        die();
                    }
                }
                if ($photo->file_id == $filesTable->lookupFile($photo->file_id, 'thumb.icon')) {
                    try {
                        $tmpFile = $newStorageFile->temporary();
                        $image = Engine_Image::factory();
                        $image->open($tmpFile);
                        $size = min($image->height, $image->width);
                        $x = ($image->width - $size) / 2;
                        $y = ($image->height - $size) / 2;
                        $image->resample($x, $y, $size, $size, 48, 48)
                            ->write($tmpFile)
                            ->destroy();
                        $iSquare = $filesTable->createFile($tmpFile, array(
                            'parent_type' => $user->getType(),
                            'parent_id' => $user->getIdentity(),
                            'user_id' => $user->getIdentity(),
                            'name' => basename($tmpFile),
                        ));
                        $newStorageFile->bridge($iSquare, 'thumb.icon');
                        @unlink($tmpFile);
                    } catch (Exception $e) {
                        echo $e;
                        die();
                    }
                }

                // Set it
                $user->photo_id = $photo->file_id;
                $user->save();

                // Insert activity
                // @todo maybe it should read "changed their profile photo" ?
                $action = Engine_Api::_()->getDbtable('actions', 'activity')
                    ->addActivity($user, $user, 'profile_photo_update',
                        '{item:$subject} changed their profile photo.');
                if ($action) {
                    // We have to attach the user himself w/o album plugin
                    Engine_Api::_()->getDbtable('actions', 'activity')
                        ->attachActivity($action, $photo);
                }
            }

            // Otherwise copy to the profile album
            else {
                $user->setPhoto($photo);

                // Insert activity
                $action = Engine_Api::_()->getDbtable('actions', 'activity')
                    ->addActivity($user, $user, 'profile_photo_update',
                        '{item:$subject} added a new profile photo.');

                // Hooks to enable albums to work
                $newStorageFile = Engine_Api::_()->getItem('storage_file', $user->photo_id);
                $event = Engine_Hooks_Dispatcher::_()
                    ->callEvent('onUserPhotoUpload', array(
                        'user' => $user,
                        'file' => $newStorageFile,
                    ));

                $attachment = $event->getResponse();
                if (!$attachment) {
                    $attachment = $newStorageFile;
                }

                if ($action) {
                    // We have to attach the user himself w/o album plugin
                    Engine_Api::_()->getDbtable('actions', 'activity')
                        ->attachActivity($action, $attachment);
                }
            }

            $db->commit();
        }

            // Otherwise it's probably a problem with the database or the storage system (just throw it)
        catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        return $this->_forward('success', 'utility', 'core', array(
            'messages' => array(Zend_Registry::get('Zend_Translate')->_('Set as profile photo')),
            'smoothboxClose' => true,
        ));
    }

    public function clearStatusAction()
    {
        $this->view->status = false;

        if ($this->getRequest()->isPost()) {
            $viewer = Engine_Api::_()->user()->getViewer();
            $viewer->status = '';
            $viewer->status_date = '00-00-0000';
            // twitter-style handling
            // $lastStatus = $viewer->status()->getLastStatus();
            // if( $lastStatus ) {
            //   $viewer->status = $lastStatus->body;
            //   $viewer->status_date = $lastStatus->creation_date;
            // }
            $viewer->save();

            $this->view->status = true;
        }
    }

    public function profilePhotosAction()
    {
        $this->view->user = $user = Engine_Api::_()->core()->getSubject();
        $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();

        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
            return $this->_helper->redirector->gotoRoute(['action' => 'manage'], 'album_general', true);
        }

        $fileTable = Engine_Api::_()->getDbtable('files', 'storage');
        $fileSelect = $fileTable->select()
            ->where("user_id = ?", $viewer->getIdentity())
            ->where("parent_type = ?", "user")
            ->where("parent_id = ?", $viewer->getIdentity())
            ->where('parent_file_id is NULL');
        $this->view->paginator = $paginator = Zend_Paginator::factory($fileSelect);
        $paginator->setItemCountPerPage(20);
        $paginator->setCurrentPageNumber($this->_getParam('page', 1));
    }

    public function deleteProfilePhotosAction()
    {
        $photoIds = (array) $this->_getParam('photo_ids');
        if (empty($photoIds)) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        }

        $viewerId = Engine_Api::_()->user()->getViewer()->getIdentity();
        $fileTable = Engine_Api::_()->getDbtable('files', 'storage');
        $attachmentTable = Engine_Api::_()->getDbtable('attachments', 'activity');
        $fileSelect = $fileTable->select()
            ->where("user_id = ?", $viewerId)
            ->where('file_id IN (?)', $photoIds)
            ->orWhere('parent_file_id IN (?)', $photoIds);
        $files = $fileTable->fetchAll($fileSelect);
        foreach($files as $file) {
            $file = Engine_Api::_()->getItem('storage_file', $file->file_id);
            // Delete attachments
            $attachmentSelect = $attachmentTable->select()
                ->where('type = ?', $file->getType())
                ->where('id = ?', $file->getIdentity())
                ;

            $attachmentActionIds = array();
            foreach( $attachmentTable->fetchAll($attachmentSelect) as $attachmentRow ) {
                $attachmentActionIds[] = $attachmentRow->action_id;
            }

            if( !empty($attachmentActionIds) ) {
                $attachmentTable->delete('action_id IN('.join(',', $attachmentActionIds).')');
                Engine_Api::_()->getDbtable('stream', 'activity')->delete('action_id IN('.join(',', $attachmentActionIds).')');
            }

            $file->delete();
        }

        $user = Engine_Api::_()->getItem('user', $viewerId);
        if (in_array($user->photo_id, $photoIds)) {
            $user->photo_id = 0;
        }
        $user->save();

        $this->_helper->redirector->gotoRoute(array(
            'controller' => 'edit',
            'action' => 'profile-photos',
        ), 'user_extended', true);
    }
}
