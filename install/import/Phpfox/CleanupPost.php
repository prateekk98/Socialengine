<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    CleanupPre.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_CleanupPost extends Install_Import_Phpfox_Abstract
{

  protected $_toTableTruncate = false;
  protected $_priority = 0;

  protected function _run()
  {
    $this->getToDb()->query("DELETE FROM engine4_album_photos WHERE file_id=0");
    $this->getToDb()->
      query("
            DELETE FROM engine4_album_albums where album_id in 
            (
                SELECT * FROM (
                                    SELECT engine4_album_albums.album_id 
                                    FROM engine4_album_albums
                                    LEFT JOIN engine4_album_photos ON engine4_album_albums.album_id = engine4_album_photos.album_id
                                    where engine4_album_photos.photo_id is null
                                ) as p 
            );
        ");
  }

  protected function _translateRow(array $data, $key = null)
  {
    return false;
  }
}
