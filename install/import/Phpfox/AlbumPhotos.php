<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    AlbumPhotos.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_AlbumPhotos extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_priority = 94;
  protected $_isTableExist = array('engine4_album_albums');
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'photo';
    $this->_toTable = 'engine4_album_photos';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
    $prefix = $this->getfromPrefix();
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

  //protected $_fromWhere = array('type_id<>?' => 2);

  protected function _translateRow(array $data, $key = null)
  {

    //WE ARE SKIPPING THE PHOTOS WHICH IS BELONGS TO PAGE
    $photo_user_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'user', 'user_id')
      ->where('profile_page_id != ?', 0)
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetchColumn();

    if( $photo_user_id ) {
      return false;
    }

    //MAKING ALBUM PHOTO ARRAY FOR INSERTION
    $newData = array();
    $newData['photo_id'] = $data['photo_id'];
    $newData['album_id'] = $data['album_id'];
    $newData['title'] = '';
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['owner_type'] = 'user';
    $newData['owner_id'] = $data['user_id'];
    $newData['comment_count'] = $data['total_comment'];
    $newData['view_count'] = $data['total_view'];
    $newData['order'] = $data['ordering'];


    if( $this->_columnExist($this->getToTable(), 'like_count') )
      $newData['like_count'] = $data['total_like'];

    //PHOTO DESTINATION AND INSERT THE PHOTO
    if( $data['destination'] ) {
      $phDest = $this->getStringBetween($data['destination'], '{', '}');
      if( !empty($phDest) && count($phDest) > 0 )
        $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
      else {
        $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/photo' . DIRECTORY_SEPARATOR . $data['destination'];
      }
      $des = explode('%s', $srcPath);
      $file = $des[0];
      if( isset($des[1]) )
        $file = $des[0] . $des[1];

      if( $file ) {
        try {
          $fileInfo = array(
            'parent_type' => 'album_photo',
            'parent_id' => $data['album_id'],
            'user_id' => $data['user_id'],
          );
          if( $this->getParam('resizePhotos', true) ) {
            $file_id = $this->_translatePhoto($file, $fileInfo);
          } else {
            $file_id = $this->_translateFile($file, $fileInfo, true);
          }
          if( empty($file_id) ) {
            $file_id = $this->createFileDiffSize($file, $fileInfo, $this->_fileSize);
          }
        } catch( Exception $e ) {
          $file_id = null;
          $this->_logFile($e->getMessage());
        }

        if( empty($file_id) ) {
          $this->_logFile("Album photo not found . Photo path " . $file);
          return false;
        }
        if( $file_id ) {
          $newData['file_id'] = $file_id;

          $album_id = $data['album_id'];
          if( !$album_id ) {
            $privacy = 0;
              //CHECKING FOR WALL ALBUM EXIST OR NOT
              $album_id = $this->getToDb()->select()
                ->from('engine4_album_albums', 'album_id')
                ->where('type =?', 'wall')
                ->where('owner_id =?', $data['user_id'])
                ->query()
                ->fetchColumn();
              //IF NOT THEN INSERT
              if( !$album_id ) {
                //INSERT THE WALL PHOTO ALBUM

                //SET PRIVACY
                $albumPrivacy = $this->_translateAlbumPrivacy($data['privacy']);

                $this->getToDb()->insert('engine4_album_albums', array(
                  'title' => 'Wall Photos',
                  'owner_type' => 'user',
                  'owner_id' => $data['user_id'],
                  'creation_date' => $this->_translateTime($data['time_stamp']),
                  'modified_date' => $this->_translateTime($data['time_stamp']),
                  'type' => 'wall',
                  'view_privacy' => $albumPrivacy[0],
                ));
                $album_id = $this->getToDb()->lastInsertId();
                //PRIVACY
                $this->_insertPrivacy('album', $album_id, 'view', $this->_translateAlbumPrivacy($privacy));
                $this->_insertPrivacy('album', $album_id, 'comment', $this->_translateAlbumPrivacy($privacy));
                $this->_insertPrivacy('album', $album_id, 'tag', $this->_translateAlbumPrivacy($privacy));
              }

          }

          $albumUpdt = array(
            'photo_id' => $data['photo_id'],
          );
          if( $this->_columnExist('engine4_album_albums', 'photos_count') ) {

            $albumUpdt['photos_count'] = new Zend_Db_Expr('photos_count + 1');
          }
          //UPDATE ALBUM
          $this->getToDb()->update('engine4_album_albums', $albumUpdt, array(
            'album_id = ?' => $album_id,
          ));
          $newData['album_id'] = $album_id;
        }
      }
    }

    //PHOTO TAGGING
    $phototags = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'photo_tag', '*')
      ->where('photo_id = ?', $data['photo_id'])
      ->query()
      ->fetchAll();
    foreach( $phototags as $phototag ) {
      if( $phototag['tag_user_id'] == 0 )
        continue;
      $this->getToDb()->insert('engine4_core_tagmaps', array(
        'resource_type' => 'album_photo',
        'resource_id' => $phototag['photo_id'],
        'tagger_type' => 'user',
        'tagger_id' => $phototag['user_id'],
        'tag_type' => 'user',
        'tag_id' => $phototag['tag_user_id'],
        'creation_date' => $this->_translateTime($phototag['time_stamp']),
        'extra' => '{"x":"' . @$phototag['position_x'] . '","y":"' . @$phototag['position_y'] . '","w":"' . @$phototag['width'] . '","h":"' . @$phototag['height'] . '"}'
      ));
    }


    //GET CATEGORY DATA
    $categoryDataInfo = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'photo_category_data', 'category_id')
      ->join($this->getfromPrefix() . 'photo', $this->getfromPrefix() . 'photo.photo_id=' . $this->getfromPrefix() . 'photo_category_data.photo_id', 'album_id')
      ->where($this->getfromPrefix() . 'photo_category_data.photo_id = ?', $data['photo_id'])
      ->query()
      ->fetchAll();

    //UPDATE CATEGORY
    foreach( $categoryDataInfo as $category ) {
      $this->getToDb()->update('engine4_album_albums', array(
        'category_id' => $category['category_id'],
        ), array(
        'album_id = ?' => $category['album_id'],
      ));
    }

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo` (
  `photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `module_id` varchar(75) DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type_id` tinyint(3) NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `destination` varchar(255) DEFAULT NULL,
  `server_id` tinyint(3) NOT NULL,
  `mature` tinyint(1) NOT NULL DEFAULT '0',
  `allow_comment` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rate` tinyint(1) NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_download` int(10) unsigned NOT NULL DEFAULT '0',
  `total_rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `total_vote` int(10) unsigned NOT NULL DEFAULT '0',
  `total_battle` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `allow_download` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `is_profile_photo` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`,`view_id`),
  KEY `photo_id` (`photo_id`,`album_id`,`view_id`,`group_id`,`privacy`),
  KEY `view_id_2` (`view_id`,`group_id`,`type_id`,`privacy`),
  KEY `photo_id_2` (`photo_id`,`album_id`,`view_id`,`group_id`,`type_id`,`privacy`),
  KEY `view_id` (`view_id`),
  KEY `privacy` (`privacy`,`allow_rate`),
  KEY `view_id_3` (`view_id`,`group_id`,`type_id`,`user_id`),
  KEY `album_id_2` (`album_id`,`view_id`,`is_cover`),
  KEY `view_id_4` (`view_id`,`privacy`,`title`),
  KEY `view_id_5` (`view_id`,`module_id`,`group_id`,`privacy`),
  KEY `is_profile_photo` (`is_profile_photo`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `tag_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `content` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `position_x` smallint(4) unsigned NOT NULL DEFAULT '0',
  `position_y` smallint(4) unsigned NOT NULL DEFAULT '0',
  `width` smallint(4) unsigned NOT NULL DEFAULT '0',
  `height` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tag_id`),
  KEY `photo_id` (`photo_id`),
  KEY `photo_id_2` (`photo_id`,`position_x`,`position_y`,`width`,`height`),
  KEY `photo_id_3` (`photo_id`,`tag_user_id`),
  KEY `photo_id_4` (`photo_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_category_data` (
  `photo_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `photo_id` (`photo_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 *
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_album_photos` (
  `photo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `order` int(11) unsigned NOT NULL DEFAULT '0',
  `owner_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`),
  KEY `owner_type` (`owner_type`,`owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_album_albums` (
  `album_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `owner_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `type` enum('wall','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','wall_onlyme','wall_friend','wall_network','profile','message','blog','cover') COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`album_id`),
  KEY `owner_type` (`owner_type`,`owner_id`),
  KEY `category_id` (`category_id`),
  KEY `search` (`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_tagmaps` (
  `tagmap_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `tagger_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tagger_id` int(11) unsigned NOT NULL,
  `tag_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tag_id` int(11) unsigned NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `extra` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`tagmap_id`),
  KEY `resource_type` (`resource_type`,`resource_id`),
  KEY `tagger_type` (`tagger_type`,`tagger_id`),
  KEY `tag_type` (`tag_type`,`tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */
