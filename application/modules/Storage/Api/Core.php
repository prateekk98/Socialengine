<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Storage_Api_Core extends Core_Api_Abstract
{
  const SPACE_LIMIT_REACHED_CODE = 3999;
  
  public function getService($serviceIdentity = null)
  {
    return Engine_Api::_()->getDbtable('services', 'storage')
      ->getService($serviceIdentity);
  }

  public function get($id, $relationship = null)
  {
    return Engine_Api::_()->getItemTable('storage_file')
        ->getFile($id, $relationship);
  }

  public function lookup($id, $relationship)
  {
    return Engine_Api::_()->getItemTable('storage_file')
        ->lookupFile($id, $relationship);
  }

  public function create($file, $params)
  {
    return Engine_Api::_()->getItemTable('storage_file')
        ->createFile($file, $params);
  }

  public function getStorageLimits()
  {
    return Engine_Api::_()->getItemTable('storage_file')
        ->getStorageLimits();
  }
  
  //$file_id is file id or storage path of image
  public function deleteExternalsFiles($file_id) {
  
    if(is_int($file_id)) {
      $file = Engine_Api::_()->getItem('storage_file', $file_id);
      if($file && $file->service_id != 1) {
        $service = Engine_Api::_()->getDbTable('services', 'storage')->getService($file->service_id);
        $service->removeFile($file->storage_path);
      }
    } else if(is_string($file_id)) {
      $serviceId = Engine_Api::_()->getDbTable('files', 'storage')->getServiceId($file_id);
      if($serviceId != '1') {
        $service = Engine_Api::_()->getDbTable('services', 'storage')->getService($serviceId);
        $service->removeFile($file_id);
      }
    }
  }
}
