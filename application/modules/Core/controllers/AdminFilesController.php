<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminFilesController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_AdminFilesController extends Core_Controller_Action_Admin
{
  protected $_basePath;

  public function init()
  {
    // Check if folder exists and is writable
    if( !file_exists(APPLICATION_PATH . '/public/admin') ||
        !is_writable(APPLICATION_PATH . '/public/admin') ) {
      return $this->_forward('error', null, null, array(
        'message' => 'The public/admin folder does not exist or is not ' .
            'writable. Please create this folder and set full permissions ' .
            'on it (chmod 0777).',
      ));
    }
    
    // Set base path
    $this->_basePath = realpath(APPLICATION_PATH . '/public/admin');
  }
  
  public function sinkAction() {

    $viewer_id = Engine_Api::_()->user()->getViewer()->getIdentity();
    
    $filesTable = Engine_Api::_()->getDbtable('files', 'core');
    $storage = Engine_Api::_()->getItemTable('storage_file');
      
    $path = $this->_getPath();
    $it = new DirectoryIterator($path);
    foreach( $it as $key => $file ) {
      $filename = $file->getFilename();
      if( ($it->isDot() && $this->_basePath == $path) || $filename == '.' || ($filename != '..' && $filename[0] == '.') ) { 
        continue;
      }
      
      $ext = strtolower(ltrim(strrchr($file->getFilename(), '.'), '.'));
      $pathFile = $this->_getPath() . DIRECTORY_SEPARATOR . $filename;
      try {
        if(is_file($pathFile)) {
          $row = $filesTable->createRow();
          $row->setFromArray(array('name' => $filename, 'extension' => $ext));
          $row->save();

          $storageObject = $storage->createFile($pathFile, array(
            'parent_id' => $row->getIdentity(),
            'parent_type' => 'core_file',
            'user_id' => $viewer_id,
          ));
          $row->storage_path = $storageObject->storage_path;
          $row->storage_file_id = $storageObject->file_id;
          $row->save();
        }
      } catch(Exception $e) {
        //silence
      }
    }
    $this->_forward('success', 'utility', 'core', array(
      'smoothboxClose' => 10,
      'parentRefresh' => 10,
      'messages' => array('You have successfully sinked all existing files.')
    ));
  }
  
  public function indexAction() {
    
    $this->view->path = $path = $this->_getPath();
    $this->view->existingFiles = count(array_diff(scandir($path), array('..', '.', 'index.html')));
    
    $this->view->formFilter = $formFilter = new Core_Form_Admin_File_Filter();
    $values = $searchValues = array();
    if ($formFilter->isValid($this->_getAllParams()))
      $values = $formFilter->getValues();
    $this->view->assign($values);
    
    $name = $this->_getParam('name', null);
    $extension = $this->_getParam('extension', null);
    if(!empty($name))
      $searchValues['name'] = $name;
    if(!empty($extension))
      $searchValues['extension'] = $extension;
        
    if ($this->getRequest()->isPost()) {
      $values = $this->getRequest()->getPost();
      foreach ($values as $key => $value) {
        if ($key == 'delete_' . $value) {
          $file = Engine_Api::_()->getItem('core_file', $value);
          if($file)
            $file->delete();
        }
      }
    }
    $this->view->paginator = Engine_Api::_()->getDbTable('files','core')->getPaginator($searchValues);
    $this->view->paginator->setItemCountPerPage(10);
    $this->view->paginator->setCurrentPageNumber($this->_getParam('page',1));
  }

  public function renameAction() {

    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = $form = new Core_Form_Admin_File_Rename();
    
    $file = Engine_Api::_()->getItem('core_file', $this->_getParam('file_id'));
    $form->populate($file->toArray());
    
    if ($this->getRequest()->isPost()) {
      if (!$form->isValid($this->getRequest()->getPost()))
        return;
      $table = Engine_Api::_()->getDbtable('files', 'core');
      $db = $table->getAdapter();
      $db->beginTransaction();
      try {
        $values = $form->getValues();
        $file->setFromArray($values);
        $file->save();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('File renamed successfully.')
      ));
    }
  }
  
  public function deleteAction() {
  
    $this->_helper->layout->setLayout('admin-simple');
    $this->view->form = new Core_Form_Admin_File_Delete();
    $item = Engine_Api::_()->getItem('core_file', $this->_getParam('file_id'));

    if ($this->getRequest()->isPost()) {
      $db = Engine_Db_Table::getDefaultAdapter();
      $db->beginTransaction();
      try {
        $item->delete();
        $db->commit();
      } catch (Exception $e) {
        $db->rollBack();
        throw $e;
      }
      $this->_forward('success', 'utility', 'core', array(
        'smoothboxClose' => 10,
        'parentRefresh' => 10,
        'messages' => array('You have successfully deleted this entry.')
      ));
    }
  }

  public function previewAction()
  {
    // Get path
    $path = $this->_getRelPath('path');

    if( file_exists($path) && is_file($path) ) {
      echo file_get_contents($path);
    }
    exit(); // ugly
  }
  
  public function downloadAction() {
  
    $file = Engine_Api::_()->getItem('core_file', $this->_getParam('file_id'));

    $storageTable = Engine_Api::_()->getDbTable('files', 'storage');
    $select = $storageTable->select()->from($storageTable->info('name'), array('storage_path', 'name'))->where('file_id = ?', $file->storage_file_id);
    $storageData = $storageTable->fetchRow($select);
    
    $storage = Engine_Api::_()->getItem('storage_file', $file->storage_file_id);
    $basePath = $storage->map();
    if($storage->service_id == 1)
      $basePath = APPLICATION_PATH . '/' . $storageData->storage_path;
      
    $storageData = (object) $storageData->toArray();
    if (empty($storageData->name) || $storageData->name == '' || empty($storageData->storage_path) || $storageData->storage_path == '')
      return;
    
    if($storage->service_id != 1) {
      
      $details = Engine_Api::_()->getDbTable('services', 'storage')->getServiceDetails();
      $config = Zend_Json::decode($details->config);

      $s3 = new Zend_Service_Amazon_S3($config['accessKey'], $config['secretKey'], $config['region']);
      $object = $s3->getObject($config['bucket'].'/'. $storageData->storage_path);
      $info = $s3->getInfo($config['bucket'].'/'. $storageData->storage_path);

      header("Content-Disposition: attachment; filename=" . urlencode(basename($storageData->name)), true);
      header("Content-Transfer-Encoding: Binary", true);
      header('Content-Type: ' . $info['type']);
      header("Content-Type: application/force-download", true);
      header("Content-Type: application/octet-stream", true);
      header("Content-Type: application/download", true);
      header("Content-Description: File Transfer", true);
      header("Content-Length: " . $info['size'], true);
      header('Expires: 0');
      header('Cache-Control: must-revalidate');
      header('Pragma: public');
      //send file to browser for download. 
      ob_clean();
      flush();
      echo $object;
      exit();
    } else {
      @chmod($basePath, 0777);
      header("Content-Disposition: attachment; filename=" . urlencode(basename($storageData->name)), true);
      header("Content-Transfer-Encoding: Binary", true);
      header("Content-Type: application/force-download", true);
      header("Content-Type: application/octet-stream", true);
      header("Content-Type: application/download", true);
      header("Content-Description: File Transfer", true);
      header("Content-Length: " . filesize($basePath), true);
      readfile("$basePath");
      exit();
      // for safety resason double check
      return;
    }
  }

  public function uploadAction()
  {
    $this->view->path = $path = $this->_getPath();
    $this->view->relPath = $relPath = $this->_getRelPath($path);
    
    // Check method
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    // Prepare
    if( empty($_FILES['Filedata']) ) {
      $this->view->error = 'File failed to upload. Check your server settings (such as php.ini max_upload_filesize).';
      return;
    }

    // Prevent evil files from being uploaded
    $disallowedExtensions = array('php');
    $parts = explode(".", $_FILES['Filedata']['name']);
    $name = end($parts);
    if( is_array($name) && in_array($name, $disallowedExtensions) ) {
      $this->view->error = 'File type or extension forbidden.';
      return;
    }
    
    $fileNameExist = Engine_Api::_()->getDbTable('files', 'core')->getFileNameExist($parts[0]);
    if(isset($fileNameExist) && !empty($fileNameExist)) {
      $this->view->error = 'File already exists. Please trying to upload another file.';
      return;
    }

    $info = $_FILES['Filedata'];
    $targetFile = $path . '/' . $info['name'];
    $vals = array();
    
    $filesTable = Engine_Api::_()->getDbtable('files', 'core');
    $storage = Engine_Api::_()->getItemTable('storage_file');

    $db = Engine_Db_Table::getDefaultAdapter();
    $db->beginTransaction();
    try {
      $row = $filesTable->createRow();
      $row->setFromArray(array());
      $row->save();
      
      if(!empty($info['name'])) {
        $extension = pathinfo($info['name']);
        $extension = $extension['extension'];
        $row->name = $parts[0];
        $row->extension = $extension;
        $row->save();

        $storageObject = $storage->createFile($info, array(
          'parent_id' => $row->getIdentity(),
          'parent_type' => 'core_file',
          'user_id' => Engine_Api::_()->user()->getViewer()->getIdentity(),
        ));
        // Remove temporary file
        @unlink($info['tmp_name']);
        $row->storage_path = $storageObject->storage_path;
        $row->storage_file_id = $storageObject->file_id;
        $row->save();
      }
      $db->commit();
    } catch(Exception $e) {
      //$db->rollBack();
      //throw $e;
    }

//     if( file_exists($targetFile) ) {
//       $deleteUrl = $this->view->url(array('action' => 'delete')) . '?path=' . $relPath . '/' . $info['name'];
//       $deleteUrlLink = '<a href="'.$this->view->escape($deleteUrl) . '">' . Zend_Registry::get('Zend_Translate')->_("delete") . '</a>';
//       $this->view->error = Zend_Registry::get('Zend_Translate')->_(sprintf("File already exists. Please %s before trying to upload.", $deleteUrlLink));
//       return;
//     }
// 
//     if( !is_writable($path) ) {
//       $this->view->error = Zend_Registry::get('Zend_Translate')->_('Path is not writeable. Please CHMOD 0777 the public/admin directory.');
//       return;
//     }
//     
//     // Try to move uploaded file
//     if( !move_uploaded_file($info['tmp_name'], $targetFile) ) {
//       $this->view->error = Zend_Registry::get('Zend_Translate')->_("Unable to move file to upload directory.");
//       return;
//     }

    $this->view->status = 1;
    $this->view->fileName = $_FILES['Filedata']['name'];
  }

  public function errorAction()
  {
    $this->getResponse()->setBody($this->view->translate($this->_getParam('message', 'error')));
    $this->_helper->viewRenderer->setNoRender(true);
  }

  protected function _getPath($key = 'path')
  {
    return $this->_checkPath(urldecode($this->_getParam($key, '')), $this->_basePath);
  }

  protected function _getRelPath($path, $basePath = null)
  {
    if( null === $basePath ) $basePath = $this->_basePath;
    $path = realpath($path);
    $basePath = realpath($basePath);
    $relPath = trim(str_replace($basePath, '', $path), '/\\');
    return $relPath;
  }
  
  protected function _checkPath($path, $basePath)
  {
    // Sanitize
    //$path = preg_replace('/^[a-z0-9_.-]/', '', $path);
    $path = preg_replace('/\.{2,}/', '.', $path);
    $path = preg_replace('/[\/\\\\]+/', '/', $path);
    $path = trim($path, './\\');
    $path = $basePath . '/' . $path;

    // Resolve
    $basePath = realpath($basePath);
    $path = realpath($path);
    
    // Check if this is a parent of the base path
    if( $basePath != $path && strpos($basePath, $path) !== false ) {
      return $this->_helper->redirector->gotoRoute(array());
    }

    return $path;
  }
}
