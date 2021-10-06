<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: CoverphotoController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_CoverphotoController extends Core_Controller_Action_Standard {

  public function getCoverPhotoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->user = $user = Engine_Api::_()->getItem('user', $this->_getParam("user_id"));
    $this->view->can_edit = $can_edit = (int) $user->authorization()->isAllowed($viewer, 'edit') &&
      Engine_Api::_()->authorization()->isAllowed('user', $user, 'coverphotoupload');

    $this->view->photo = $photo = Engine_Api::_()->getItem('storage_file', $user->coverphoto);
    $this->view->topPosition = 0;
    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->level_id = $level_id = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $user->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }
    if ($photo && empty($uploadDefaultCover)) {
      $coverPhotoParams = is_array($user->coverphotoparams) ? $user->coverphotoparams :
        Zend_Json_Decoder::decode($user->coverphotoparams);
      $this->view->topPosition = $coverPhotoParams['top'];
    } else {
      $coverPhotoParams = Zend_Json_Decoder::decode(Engine_Api::_()->getApi("settings", "core")->getSetting(
        "usercoverphoto.preview.level.params.$user->level_id",
        Zend_Json_Encoder::encode(array('top' => '0', 'left' => 0))
      ));
      $this->view->topPosition = $coverPhotoParams['top'];
    }
  }

  public function getMainPhotoAction() {
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->user = $user = Engine_Api::_()->getItem('user', $this->_getParam("user_id"));
    $this->view->uploadDefaultCover = 0;
    $this->view->auth = $user->authorization()->isAllowed($viewer, 'view');
    $this->view->userNavigation = Engine_Api::_()->getApi('menus', 'core')->getNavigation('user_profile');
    $this->view->editIcon = 0;
    if($user->getOwner()->isSelf($viewer)){
      $this->view->editIcon = 1;
    }
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $user->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
    }
    $this->view->can_edit = $can_edit = $user->authorization()->isAllowed($viewer, 'edit');
    $this->view->photo = $photo = Engine_Api::_()->getItem('storage_file', $user->coverphoto);
    $this->view->level_id = $level_id = $this->_getParam("level_id", $user->getOwner()->level_id);
  }

  public function resetCoverPhotoPositionAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $user_id = $this->_getParam("user_id");
    $this->view->user = $user = Engine_Api::_()->getItem('user', $user_id);
    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->level_id = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $user->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }
    if (!$uploadDefaultCover) {
      $this->view->can_edit = $can_edit = (int) $user->authorization()->isAllowed($viewer, 'edit') &&
        Engine_Api::_()->authorization()->isAllowed('user', $user, 'coverphotoupload');
      if (empty($can_edit)) {
        return;
      }

      $this->view->photo = $photo = Engine_Api::_()->getItem('storage_file', $user->coverphoto);
      if (empty($uploadDefaultCover)) {
        $user->coverphotoparams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => '0', 'left' => 0)));
        $user->save();
      }
    } else {
      $postionParams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => '0', 'left' => 0)));
      Engine_Api::_()->getApi("settings", "core")
        ->setSetting("usercoverphoto.preview.level.params.$level_id", $postionParams);
    }
  }

  public function chooseFromAlbumsAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->_helper->layout->setLayout('default-simple');
    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->photoType = $photoType = $this->_getParam('photoType', 'cover');
    $this->view->user = $user = Engine_Api::_()->getItem('user', $this->_getParam("user_id"));
    if ($photoType == 'cover') {
      $this->view->can_edit = $can_edit = (int) $user->authorization()->isAllowed($viewer, 'edit') &&
        Engine_Api::_()->authorization()->isAllowed('user', $user, 'coverphotoupload');
      if (!$can_edit) {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }

    $this->view->recentAdded = $recentAdded = $this->_getParam("recent", false);
    $this->view->album_id = $album_id = $this->_getParam("album_id");
    $paginator = '';
    if ($album_id) {
      $this->view->album = $album = Engine_Api::_()->getItem('album', $album_id);
      $this->view->paginator = $paginator = Engine_Api::_()->getItemTable('album_photo')->getPhotoPaginator(array('album' => $album));
    } elseif ($recentAdded) {
      $select = Engine_Api::_()->getItemTable('album')->getAlbumSelect(array('owner' => $user));
      $albums = $select->query()->fetchAll(Zend_Db::FETCH_COLUMN);
      $paginator = $this->getPhotoPaginator($albums);
    } else {
      $paginator = Engine_Api::_()->getItemTable('album')->getAlbumPaginator(array('owner' => $user));
    }
    $this->view->paginator = $paginator;
  }

  public function uploadCoverPhotoAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->_helper->layout->setLayout('default-simple');
    if (!$this->_helper->requireUser()->checkRequire()) {
      $this->view->status = false;
      $this->view->error = Zend_Registry::get('Zend_Translate')->_('Max file size limit exceeded.');
      return;
    }

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->photoType = $photoType = $this->_getParam('photoType', 'cover');
    $user = Engine_Api::_()->getItem('user', $this->_getParam('user_id'));
    $this->view->level_id = $level_id = 0;
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $user->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }

    if ($photoType == 'cover') {
      if (!$uploadDefaultCover) {
        $this->view->can_edit = $can_edit = (int) $user->authorization()->isAllowed($viewer, 'edit') &&
          Engine_Api::_()->authorization()->isAllowed('user', $user, 'coverphotoupload');

        if (!$can_edit) {
          return $this->_forward('requireauth', 'error', 'core');
        }
        $this->view->form = $form = new User_Form_CoverPhoto_Cover();
      } else {
        $this->view->form = $form = new User_Form_CoverPhoto_Cover();
      }
    } else {
      $this->view->form = $form = new User_Form_CoverPhoto_Cover();
      $form->setTitle('Upload Profile Picture');
      $form->setAttrib('name', 'Upload a Profile Picture');
      $form->Filedata->setLabel('Choose a profile picture.');
    }

    if (empty($uploadDefaultCover)) {
      $file = '';
      $photo = null;
      $alreadyHasCover = false;
      $photo_id = $this->_getParam('photo_id');
      if ($photo_id) {
        $photo = Engine_Api::_()->getItem('album_photo', $photo_id);
        $album = Engine_Api::_()->getItem('album', $photo->album_id);

        if ($album && ($album->type == 'cover' || $album->type == 'profile')) {
          $alreadyHasCover = true;
        }
        if ($photo->file_id && !$alreadyHasCover) {
          $photo = Engine_Api::_()->getItemTable('storage_file')->getFile($photo->file_id);
        }
      }

      if (empty($photo_id) || empty($photo)) {
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
          return;
        }
      }

      if ($form->Filedata->getValue() !== null || $photo || $alreadyHasCover) {

        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        try {
          if (!$alreadyHasCover) {
            if ($photo) {
              if ($photoType == 'cover') {
                $user = $this->setCoverPhoto($photo, $user);
              } else {
                $user = $this->setMainPhoto($photo, $user);
              }
            } else {
              if ($photoType == 'cover') {
                $user = $this->setCoverPhoto($form->Filedata, $user);
                $photo = Engine_Api::_()->getItem('storage_file', $user->coverphoto);
              } else {
                $user = $this->setMainPhoto($form->Filedata, $user);
                $photo = Engine_Api::_()->getItem('storage_file', $user->photo_id);
              }
            }
          }
          if ($photoType == 'cover') {
            $actionType = 'cover_photo_update';
            $type = 'cover';
            $user->coverphoto = $photo->file_id;
          } else {
            $actionType = 'profile_photo_update';
            $type = 'profile';
            $user->photo_id = $photo->file_id;
          }
          $user->coverphotoparams = Zend_Json_Encoder::encode($this->_getParam('position', array('top' => 0, 'left' => 0)));
          $user->save();

          // Insert Activity
          $action = Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, $actionType);
          // Hooks to enable albums to work
          if ($action) {
            $event = Engine_Hooks_Dispatcher::_()
              ->callEvent('onUserPhotoUpload', array(
              'user' => $user,
              'file' => $photo,
              'type' => $type,
              ));

            $attachment = $event->getResponse();
            if (empty($attachment)) {
              $attachment = $photo;
            }

            Engine_Api::_()->getDbtable('actions', 'activity')->attachActivity($action, $attachment);
          }
          $this->view->status = true;
          $db->commit();
        } catch (Exception $e) {
          $db->rollBack();
          return $this->exceptionWrapper($e, $form, $db);
        }
      }
    } else {
      if (!$form->isValid($this->getRequest()->getPost())) {
        return;
      }
      if ($form->Filedata->getValue() !== null) {
        $values = $form->getValues();
        $this->setCoverPhoto($form->Filedata, null, $level_id);
        $this->view->status = true;
      }
    }
  }

  public function removeCoverPhotoAction() {
    if (!$this->_helper->requireUser()->isValid()) {
      return;
    }

    $this->view->uploadDefaultCover = $uploadDefaultCover = 0;
    $this->view->level_id = $level_id = 0;
    $this->view->photoType = $photoType = $this->_getParam('photoType', 'cover');
    $viewer = Engine_Api::_()->user()->getViewer();
    $user = Engine_Api::_()->getItem('user', $this->_getParam('user_id'));
    if ($viewer->getIdentity() && $viewer->level_id == 1 && $user->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = $uploadDefaultCover = $this->_getParam("uploadDefaultCover", 0);
      $this->view->level_id = $level_id = $this->_getParam("level_id", 0);
    }

    if ($photoType == 'cover' && empty($uploadDefaultCover)) {
      $this->view->can_edit = $can_edit = (int) $user->authorization()->isAllowed($viewer, 'edit') &&
        Engine_Api::_()->authorization()->isAllowed('user', $user, 'coverphotoupload');
      if (!$can_edit) {
        return $this->_forward('requireauth', 'error', 'core');
      }
    }

    $coreSettingsApi = Engine_Api::_()->getApi("settings", "core");
    $preview_id = $coreSettingsApi->getSetting("usercoverphoto.preview.level.id.$level_id");
    if (!$this->getRequest()->isPost()) {
      return;
    }

    if ($photoType == 'cover') {
      if (empty($uploadDefaultCover)) {
        $this->whenRemove($user,"coverphoto");
        $user->coverphoto = 0;
        $user->coverphotoparams = Zend_Json_Encoder::encode(array('top' => '0', 'left' => 0));
      } else {
        $coreSettingsApi->setSetting("usercoverphoto.preview.level.id.$level_id", 0);
        $postionParams = Zend_Json_Encoder::encode(array('top' => '0', 'left' => 0));
        $coreSettingsApi->setSetting("usercoverphoto.preview.level.params.$level_id", $postionParams);
        $file = Engine_Api::_()->getItem('storage_file', $preview_id);
        if ($file) {
          $file->delete();
        }
      }
    } else {
      $this->whenRemove($user,"photo_id");
      $user->photo_id = 0;
    }
    $user->save();

    $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array(Zend_Registry::get('Zend_Translate')->_(''))
    ));
  }

  private function getPhotoPaginator($album_ids) {
    if (empty($album_ids)) {
      return;
    }

    $select = Engine_Api::_()->getDbtable('photos', 'album')->select();
    $select->where('album_id in (?)', $album_ids)->order('order DESC');
    return Zend_Paginator::factory($select);
  }

  private function setCoverPhoto($photo, $user, $level_id = null)
  {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = $file;
    }

    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';

    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $coreSettings = Engine_Api::_()->getApi('settings', 'core');
    $mainHeight = $coreSettings->getSetting('main.photo.height', 1600);
    $mainWidth = $coreSettings->getSetting('main.photo.width', 1600);

    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($mainWidth, $mainHeight)
      ->write($mainPath)
      ->destroy();

    $normalHeight = $coreSettings->getSetting('normal.photo.height', 375);
    $normalWidth = $coreSettings->getSetting('normal.photo.width', 375);
    // Resize image (normal)

    $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($normalWidth, $normalHeight)
      ->write($normalPath)
      ->destroy();

    $coverPath = $path . DIRECTORY_SEPARATOR . $base . '_c.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize(1500, 1500)
      ->write($coverPath)
      ->destroy();

    if (!empty($user)) {
      $params = array(
        'parent_type' => $user->getType(),
        'parent_id' => $user->getIdentity(),
        'user_id' => $user->getIdentity(),
        'name' => basename($fileName),
      );

      try {
        $iMain = $filesTable->createFile($mainPath, $params);
        $iIconNormal = $filesTable->createFile($normalPath, $params);
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iCover = $filesTable->createFile($coverPath, $params);
        $iMain->bridge($iCover, 'thumb.cover');
        if(!Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')){
          // if user coverphoto column is empty.
          if(!empty($user['coverphoto'])){
            $file = Engine_Api::_()->getItem('storage_file', $user['coverphoto']);
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
        $user->coverphoto = $iMain->file_id;
        $user->save();
      } catch (Exception $e) {
        @unlink($mainPath);
        @unlink($normalPath);
        @unlink($coverPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($coverPath);
      if (!empty($tmpRow)) {
        $tmpRow->delete();
      }
      return $user;
    } else {
      try {
        $iMain = $filesTable->createSystemFile($mainPath);
        $iIconNormal = $filesTable->createSystemFile($normalPath);
        $iMain->bridge($iIconNormal, 'thumb.normal');
        $iCover = $filesTable->createSystemFile($coverPath);
        $iMain->bridge($iCover, 'thumb.cover');
        // Remove temp files
        @unlink($mainPath);
        @unlink($normalPath);
        @unlink($coverPath);
      } catch (Exception $e) {
        @unlink($mainPath);
        @unlink($normalPath);
        @unlink($coverPath);
        if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE
          && Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
          throw new Album_Model_Exception($e->getMessage(), $e->getCode());
        } else {
          throw $e;
        }
      }
      Engine_Api::_()->getApi("settings", "core")
        ->setSetting("usercoverphoto.preview.level.id.$level_id", $iMain->file_id);
      return $user;
    }
  }

  private function setMainPhoto($photo, $user) {
    if ($photo instanceof Zend_Form_Element_File) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if ($photo instanceof Storage_Model_File) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if ($photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id)) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if (is_array($photo) && !empty($photo['tmp_name'])) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if (is_string($photo) && file_exists($photo)) {
      $file = $photo;
      $fileName = $photo;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }

    if (!$fileName) {
      $fileName = $file;
    }

    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => $user->getType(),
      'parent_id' => $user->getIdentity(),
      'user_id' => $user->getIdentity(),
      'name' => basename($fileName),
    );

    // Save
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $coreSettings = Engine_Api::_()->getApi('settings', 'core');
    $mainHeight = $coreSettings->getSetting('main.photo.height', 1600);
    $mainWidth = $coreSettings->getSetting('main.photo.width', 1600);
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($mainWidth, $mainHeight)
      ->write($mainPath)
      ->destroy();

    $normalHeight = $coreSettings->getSetting('normal.photo.height', 375);
    $normalWidth = $coreSettings->getSetting('normal.photo.width', 375);
    // Resize image (normal)
    $normalPath = $path . DIRECTORY_SEPARATOR . $base . '_in.' . $extension;

    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($normalWidth, $normalHeight)
      ->write($normalPath)
      ->destroy();

    $normalLargeHeight = $coreSettings->getSetting('normallarge.photo.height', 720);
    $normalLargeWidth = $coreSettings->getSetting('normallarge.photo.width', 720);
    // Resize image (normal)
    $normalLargePath = $path . DIRECTORY_SEPARATOR . $base . '_inl.' . $extension;

    $image = Engine_Image::factory();
    $image->open($file)
      ->resize($normalLargeWidth, $normalLargeHeight)
      ->write($normalLargePath)
      ->destroy();
    // Resize image (icon)
    $squarePath = $path . DIRECTORY_SEPARATOR . $base . '_is.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file);

    $size = min($image->height, $image->width);
    $x = ($image->width - $size) / 2;
    $y = ($image->height - $size) / 2;

    $image->resample($x, $y, $size, $size, 48, 48)
      ->write($squarePath)
      ->destroy();
        // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);
      $iIconNormal = $filesTable->createFile($normalPath, $params);
      $iMain->bridge($iIconNormal, 'thumb.normal');
      $iIconNormalLarge = $filesTable->createFile($normalLargePath, $params);
      $iMain->bridge($iIconNormalLarge, 'thumb.large');
      $iSquare = $filesTable->createFile($squarePath, $params);
      $iMain->bridge($iSquare, 'thumb.icon');
      // Remove temp files
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($normalLargePath);
      @unlink($squarePath);
    } catch (Exception $e) {
        // Remove temp files
      @unlink($mainPath);
      @unlink($normalPath);
      @unlink($normalLargePath);
      @unlink($squarePath);
      // Throw
      if ($e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE &&
        Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
        throw new Album_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    if (!empty($tmpRow)) {
      $tmpRow->delete();
    }

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

    $user->photo_id = $iMain->file_id;
    $user->save();
    return $user;
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
}
