<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Transaction.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Model_Transaction extends Core_Model_Item_Abstract
{
  protected $_searchTriggers = false;

  protected $_modifiedTriggers = false;
  
  public function setPhoto($photo){
    if( $photo instanceof Zend_Form_Element_File ) {
      $file = $photo->getFileName();
      $fileName = $file;
    } else if( $photo instanceof Storage_Model_File ) {
      $file = $photo->temporary();
      $fileName = $photo->name;
    } else if( $photo instanceof Core_Model_Item_Abstract && !empty($photo->file_id) ) {
      $tmpRow = Engine_Api::_()->getItem('storage_file', $photo->file_id);
      $file = $tmpRow->temporary();
      $fileName = $tmpRow->name;
    } else if( is_array($photo) && !empty($photo['tmp_name']) ) {
      $file = $photo['tmp_name'];
      $fileName = $photo['name'];
    } else if( is_string($photo) ) {
      $file = $photo;
      $fileName = $photo;
      $unlink = false;
    } else {
      throw new User_Model_Exception('invalid argument passed to setPhoto');
    }
    $name = basename($file);
    $extension = ltrim(strrchr($fileName, '.'), '.');
    $base = rtrim(substr(basename($fileName), 0, strrpos(basename($fileName), '.')), '.');
    if( !$fileName ) {
      $fileName = $file;
    }
    $filesTable = Engine_Api::_()->getDbtable('files', 'storage');
    $path = APPLICATION_PATH . DIRECTORY_SEPARATOR . 'temporary';
    $params = array(
      'parent_type' => $this->getType(),
      'parent_id' => $this->getIdentity(),
      'user_id' => $this->user_id,
      'name' => $fileName,
    );
    // Resize image (main)
    $mainPath = $path . DIRECTORY_SEPARATOR . $base . '_m.' . $extension;
    $image = Engine_Image::factory();
    $image->open($file)
      ->autoRotate()
      ->resize(1200, 700)
      ->write($mainPath)
      ->destroy();
		
    // Store
    try {
      $iMain = $filesTable->createFile($mainPath, $params);      
    } catch( Exception $e ) {
			@unlink($file);
      // Remove temp files
      @unlink($mainPath);
     
      // Throw
      if( $e->getCode() == Storage_Model_DbTable_Files::SPACE_LIMIT_REACHED_CODE ) {
        throw new Sesalbum_Model_Exception($e->getMessage(), $e->getCode());
      } else {
        throw $e;
      }
    }
    	if(!isset($unlink))
				@unlink($file);
    // Remove temp files
      @unlink($mainPath);
     
    // Update row
    $this->file_id = $iMain->file_id;
    $this->save();
    // Delete the old file?
    if( !empty($tmpRow) ) {
      $tmpRow->delete();
    }
    return $this;
	} 
  public function getPhotoUrl($type = null) {
    if ($this->file_id) {
      $file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id, $type);
			if($file)
      	return $file->map();
			else{
				$file = Engine_Api::_()->getItemTable('storage_file')->getFile($this->file_id,'thumb.profile');
				if($file)
					return $file->map();
			}
    }
  }
}
