<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    PagesGroups.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_PagesGroups extends Install_Import_Phpfox_Abstract
{
  protected $_fromTable = '';

  protected $_toTable = '';

  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_truncateTable($this->getToDb(), 'engine4_group_photos');
    $this->_truncateTable($this->getToDb(), 'engine4_group_albums');
    $this->_truncateTable($this->getToDb(), 'engine4_group_membership');
    $this->_truncateTable($this->getToDb(), 'engine4_group_lists');
    $this->_fromTable = $this->getFromPrefix() . 'pages';
    $this->_toTable = 'engine4_group_groups';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');

    $prefix = $this->getFromPrefix();
    $picSize = $this->getFromDb()
      ->select()
      ->from($prefix . "setting", 'value_actual')
      ->where('var_name = ?', 'photo_pic_sizes')
      ->query()
      ->fetchColumn(0);
    $this->_fileSize = null;
    if( $picSize !== false ) {
      $picSize = explode(':"', $picSize);
      if( isset($picSize[1]) ) {
        $picSize = explode(';";', $picSize[1]);
        if( isset($picSize[0]) ) {
          $picSize = $picSize[0];
          $picSizeArr = $this->getStringBetween($picSize, "=>", ",");
          foreach( $picSizeArr as $ps ) {
            $ps = str_replace("'", "", $ps);
            $dt[] = (int) $ps;
          }
          rsort($dt);
          if( isset($dt[0]) )
            $this->_fileSize = $dt[0];
        }
      }
    }
  }

  protected function _translateRow(array $data, $key = null)
  {
    $newData = array();

    //GET PAGE ID
    $pageId = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'user', 'user_id')
      ->where('profile_page_id = ?', $data['page_id'])
      ->query()
      ->fetchColumn();

    //GETTING TEXT
    $text = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'pages_text', 'text_parsed')
      ->where('page_id = ?', $data['page_id'])
      ->query()
      ->fetchColumn();

    //CHECKING TEXT IS NULL OR NOT
    if( is_null($text) || $text === false ) {
      $text = '';
    }

    $newData['group_id'] = $data['page_id'];
    $newData['user_id'] = $data['user_id'];
    $newData['title'] = $data['title'];
    $newData['description'] = $text;
    $newData['category_id'] = $data['category_id'];
    $newData['search'] = 1;
    $newData['invite'] = 1;

    if( $data['reg_method'] == 0 || $data['reg_method'] == 2 ) {
      $newData['approval'] = 0;
    } else {
      $newData['approval'] = 1;
    }

    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);

    //GET DESTINATION
    if( !empty($data['image_path']) ) {
      $album = $this->getFromDb()->query("SELECT * FROM " . $this->getFromPrefix() . "photo_album WHERE (user_id = $pageId) AND (name = '{phrase var=\'photo.profile_pictures\'}' OR name = 'Profile Pictures')")->fetch();
      if( $album ) {
        //GET DESCRIPTION
        $description = $this->getFromDb()->select()
          ->from($this->getFromPrefix() . 'photo_album_info', 'description')
          ->where('album_id = ?', $album['album_id'])
          ->query()
          ->fetchColumn();
        $this->getToDb()->insert('engine4_group_albums', array(
          'album_id' => $album['album_id'],
          'group_id' => $data['page_id'],
          'creation_date' => $this->_translateTime($data['time_stamp']),
          'modified_date' => $album['time_stamp_update'] ? $this->_translateTime($album['time_stamp_update']) : $this->_translateTime($album['time_stamp']),
          'collectible_count' => 1
        ));
        $albumId = $album['album_id'];
        $seAlbumId = $this->getToDb()->lastInsertId();
        //SELECTION OF ALL PHOTOS OF PROFILE ALBUM
        $photos = $this->getFromDb()->select()
          ->from($this->getFromPrefix() . 'photo', '*')
          ->where('album_id = ?', $albumId)
          ->query()
          ->fetchAll();
        //INSERTION OF ALL PHOTOS OF PROFILE ALBUM
        foreach( $photos as $photo ) {
          try {
            $phDest = $this->getStringBetween($photo['destination'], '[GROUP]{', '}');
            if( !empty($phDest) && count($phDest) > 0 ) {
              $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
            } else {
              $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/photo' . DIRECTORY_SEPARATOR . $photo['destination'];
            }
            $des = explode('%s', $srcPath);
            $file = $des[0];

            if( isset($des[1]) ) {
              $file = $des[0] . $des[1];
            }

            $fileInfo = array(
              'parent_type' => 'group_photo',
              'parent_id' => $photo['photo_id'],
              'user_id' => $data['user_id'],
            );
            $filePhotoId = $this->_translateFile($file, $fileInfo, true);
            if( is_null($filePhotoId) || $filePhotoId == 0 ) {
              $filePhotoId = $this->createFileDiffSize($file, $fileInfo, $this->_fileSize);
            }
          } catch( Exception $e ) {
            $filePhotoId = null;
            $this->_warning($e->getMessage(), 1);
          }

          if( empty($filePhotoId) ) {
            $this->_logFile("Group main photo not found . Photo path " . $file);
            continue;
          }

          $this->getToDb()->insert('engine4_group_photos', array(
            'photo_id' => $photo['photo_id'],
            'album_id' => $seAlbumId,
            'collection_id' => $seAlbumId,
            'group_id' => $data['page_id'],
            'creation_date' => $this->_translateTime($data['time_stamp']),
            'modified_date' => $this->_translateTime($data['time_stamp']),
            'user_id' => @$data['user_id'],
            'file_id' => $filePhotoId,
            'comment_count' => $photo['total_comment'],
            'view_count' => $photo['total_view'],
          ));

          if( $photo['is_cover'] ) {
            $newData['photo_id'] = $filePhotoId;

            $this->getToDb()->update('engine4_group_albums', array(
              'photo_id' => $filePhotoId,
              ), array(
              'album_id = ?' => $seAlbumId,
            ));
          }
        }
      } else {
        $albumId = $this->findAlbum(0, $data['page_id'], @$data['user_id'], $this->_translateTime($data['time_stamp']));

        try {
          $phDest = $this->getStringBetween($data['image_path'], '[GROUP]{', '}');

          if( !empty($phDest) && count($phDest) > 0 ) {
            $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
          } else {
            $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/photo' . DIRECTORY_SEPARATOR . $data['image_path'];
          }

          $des = explode('%s', $srcPath);
          $file = $des[0];

          if( isset($des[1]) ) {
            $file = $des[0] . $des[1];
          }

          $fileInfo = array(
            'parent_type' => 'group',
            'parent_id' => $data['page_id'],
            'user_id' => $data['user_id'],
          );

          $filePhotoId = $this->_translateFile($file, $fileInfo, true);

          if( is_null($filePhotoId) || $filePhotoId == 0 ) {
            $filePhotoId = $this->createFileDiffSize($file, $fileInfo, $this->_fileSize);
          }
        } catch( Exception $e ) {
          $filePhotoId = null;
          $this->_logFile($e->getMessage());
        }

        if( !empty($filePhotoId) ) {
          $maxId = $this->getToDb()
            ->select()
            ->from('engine4_group_photos', 'max(photo_id)')
            ->limit(1)
            ->query()
            ->fetchColumn(0);

          if( $maxId === false || $maxId < 1000000 ) {
            $maxId = 1000000;
          } else {
            $maxId++;
          }

          $this->getToDb()->insert('engine4_group_photos', array(
            'photo_id' => $maxId,
            'album_id' => $albumId,
            'collection_id' => $albumId,
            'group_id' => $data['page_id'],
            'creation_date' => $this->_translateTime($data['time_stamp']),
            'modified_date' => $this->_translateTime($data['time_stamp']),
            'user_id' => @$data['user_id'],
            'file_id' => $filePhotoId,
            'comment_count' => 0,
            'view_count' => 0,
          ));
        } else {
          $this->_logFile("Group main photo not found.Photo path " . $file);
        }
      }
    }

    $this->getToDb()->insert('engine4_group_lists', array(
      'owner_id' => $data['page_id'],
      'title' => 'GROUP_OFFICERS'
    ));
    $listId = $this->getToDb()->lastInsertId();

    //INSERTING VIEW PRIVACY
    $this->_insertPrivacy('group', $data['page_id'], 'view', $this->_translateGroupPrivacy($data['privacy'], null, $listId, 'view'));

    //GETTING THE PRIVACY
    $perms = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'pages_perm', '*')
      ->where('page_id = ?', $data['page_id'])
      ->query()
      ->fetchAll();

    $photoPrivacy = 0;
    $eventPrivacy = 0;
    $forumPrivacy = 0;
    foreach( $perms as $permissions ) {
      switch( $permissions['var_name'] ) {
        case 'photo.share_photos':
          $photoPrivacy = $permissions['var_value'];
          break;
        case 'event.share_events':
          $eventPrivacy = $permissions['var_value'];
          break;
        case 'forum.share_forum':
          $forumPrivacy = $permissions['var_value'];
          break;
      }
    }

    //INSERT PRIVACY
    $this->_insertPrivacy('group', $data['page_id'], 'photo', $this->_translateGroupPrivacy($photoPrivacy, null, $listId, 'photo'));
    $this->_insertPrivacy('group', $data['page_id'], 'event', $this->_translateGroupPrivacy($eventPrivacy, null, $listId, 'event'));
    $this->_insertPrivacy('group', $data['page_id'], 'invite', $this->_translateGroupPrivacy(0, null, $listId, 'invite'));
    $this->_insertPrivacy('group', $data['page_id'], 'comment', $this->_translateGroupPrivacy($forumPrivacy, null, $listId, 'comment'));

    //GET ALL USERS FOR THIS PAGE WHO ARE THE MEMBER OF THIS PAGE
    $userAllIds = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'friend', 'user_id')
      ->where('friend_user_id = ?', $pageId)
      ->where('is_page = ?', 1)
      ->query()
      ->fetchAll();

    foreach( $userAllIds as $userids ) {
      $this->getToDb()->insert('engine4_group_membership', array(
        'resource_id' => $data['page_id'],
        'user_id' => $userids['user_id'],
        'active' => 1,
        'resource_approved' => 1,
        'user_approved' => 1
      ));
    }

    $newData['member_count'] = count($userAllIds);

    $requestedUserAllIds = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'pages_signup', 'user_id')
      ->where('page_id = ?', $data['page_id'])
      ->query()
      ->fetchAll();

    foreach( $requestedUserAllIds as $userRequestId ) {
      $this->getToDb()->insert('engine4_group_membership', array(
        'resource_id' => $data['page_id'],
        'user_id' => $userRequestId['user_id'],
        'active' => 0,
        'resource_approved' => 0,
        'user_approved' => 1
      ));
    }

    $this->_insertPhotoExt($data, $this->_fileSize);

    return $newData;
  }

  //INSERT ALL THE PHOTO OF GROUP.
  public function _insertPhotoExt($data, $fileSize)
  {
    $photos = $this->getFromDb()
      ->select()
      ->from($this->getFromPrefix() . 'photo', '*')
      ->where('module_id = ?', 'pages')
      ->where('group_id = ?', $data['page_id'])
      ->query()
      ->fetchAll();

    $this->_insertGroupPhoto($data, $photos, $fileSize);
  }

  /**
   * Checks if album exists, if not; create it
   *
   * @param int $albumId
   * @param int $pageId
   * @param int $userId
   * @param int $timestamp
   * @return int SE album ID
   */
  public function findAlbum($albumId, $pageId, $userId, $timestamp)
  {
    $maxId = $albumId;

    $seAlbumId = $this->getToDb()->select()
      ->from('engine4_group_albums', 'album_id')
      ->where('group_id = ?', $pageId)
      ->query()
      ->fetchColumn(0);

    if( $seAlbumId === false ) {
      if( $maxId == 0 ) {
        $maxId = $this->getToDb()
          ->select()
          ->from('engine4_group_albums', 'max(album_id)')
          ->limit(1)
          ->query()
          ->fetchColumn(0);
        if( $maxId === false || $maxId < 1000000 ) {
          $maxId = 1000000;
        } else {
          $maxId++;
        }
      }

      $this->getToDb()->insert('engine4_group_albums', array(
        'album_id' => $maxId,
        'title' => '',
        'description' => '',
        'group_id' => $pageId,
        'creation_date' => $this->_translateTime($timestamp),
        'modified_date' => $this->_translateTime($timestamp),
        'collectible_count' => 1
      ));
      $seAlbumId = $this->getToDb()->lastInsertId();
    }

    return $seAlbumId;
  }

  //INSERT THE GROUP ALBUM AND PHOTO
  public function _insertGroupPhoto($data, $photos, $fileSize)
  {
    foreach( $photos as $photo ) {
      //Get Owner UserId
      $userId = $this->getFromDb()
        ->query("SELECT  " . $this->getFromPrefix() . "pages.user_id FROM " . $this->getFromPrefix() . "user left join  " . $this->getFromPrefix() . "pages on page_id=profile_page_id where profile_page_id<>0 and  " . $this->getFromPrefix() . "user.user_id=" . $photo['user_id'])
        ->fetchColumn(0);

      if( $userId ) {
        $photo['user_id'] = $userId;
      }

      $albumId = $this->findAlbum($photo['album_id'], $photo['group_id'], $photo['user_id'], $photo['time_stamp']);

      try {
        $phDest = $this->getStringBetween($photo['destination'], '[GROUP]{', '}');

        if( !empty($phDest) && count($phDest) > 0 ) {
          $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
        } else {
          $phDest = $this->getStringBetween($photo['destination'], '{', '}');

          if( !empty($phDest) && count($phDest) > 0 ) {
            $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
          } else {
            $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/photo' . DIRECTORY_SEPARATOR . $photo['destination'];
          }
        }

        $des = explode('%s', $srcPath);
        $file = $des[0];
        if( isset($des[1]) ) {
          $file = $des[0] . $des[1];
        }

        $fileInfo = array(
          'parent_type' => 'group_photo',
          'parent_id' => $photo['photo_id'],
          'user_id' => @$data['user_id'],
        );

        $filePhotoId = $this->_translateFile($file, $fileInfo, true);

        if( is_null($filePhotoId) || $filePhotoId == 0 ) {
          $filePhotoId = $this->createFileDiffSize($file, $fileInfo, $fileSize);
        }
      } catch( Exception $e ) {
        $filePhotoId = null;
        $this->_warning($e->getMessage(), 1);
      }

      if( empty($filePhotoId) ) {
        $this->_logFile("Group photo not found. file path " . $file);
        continue;
      }

      //INSERT THE GROUP PHOTO
      $this->getToDb()->insert('engine4_group_photos', array(
        'photo_id' => $photo['photo_id'],
        'album_id' => $albumId,
        'collection_id' => $albumId,
        'group_id' => $data['page_id'],
        'creation_date' => $this->_translateTime($photo['time_stamp']),
        'modified_date' => $this->_translateTime($photo['time_stamp']),
        'user_id' => @$data['user_id'],
        'file_id' => $filePhotoId,
        'comment_count' => $photo['total_comment'],
        'view_count' => $photo['total_view'],
      ));

      //UPDATE PHOTO ID
      $this->getToDb()->update('engine4_group_albums', array(
        'photo_id' => $filePhotoId,
        ), array(
        'album_id = ?' => $albumId,
      ));
    }
  }
}
