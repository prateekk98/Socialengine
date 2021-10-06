<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    ClassifiedClassifieds.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_ClassifiedClassifieds extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'marketplace';
    $this->_toTable = 'engine4_classified_classifieds';
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

  protected function _translateRow(array $data, $key = null)
  {
    //GET CATEGORY ID
    $category_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'marketplace_category_data', 'category_id')
      ->where('listing_id = ?', $data['listing_id'])
      ->query()
      ->fetchColumn();

    //GET BODY
    $body = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'marketplace_text', 'description_parsed')
      ->where('listing_id = ?', $data['listing_id'])
      ->query()
      ->fetchColumn();
    if( $body === false || is_null($body) )
      $body = '';
    //MAKING CLASSIFIED ARRAY FOR INSERTION
    $newData = array();
    $newData['classified_id'] = $data['listing_id'];
    $newData['owner_id'] = $data['user_id'];
    $newData['title'] = $data['title'];
    $newData['body'] = $body;
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['comment_count'] = $data['total_comment'];
    $newData['category_id'] = $category_id;
    $newData['search'] = 1;
    $newData['closed'] = $data['is_closed'];

    //MAKING ALBUM ARRAY FOR INSERTION
    $albumData = array();
    $albumData['classified_id'] = $data['listing_id'];
    $albumData['title'] = $data['title'];
    $albumData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $albumData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $albumData['search'] = 1;
    $albumData['comment_count'] = $data['total_comment'];
    $this->getToDb()->insert('engine4_classified_albums', $albumData);
    $album_id = $this->getToDb()->lastInsertId();

    //GET IMAGE DATA FOR CLASSIFIED
    $imageData = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'marketplace_image', '*')
      ->where('listing_id = ?', $data['listing_id'])
      ->query()
      ->fetchAll();
    $count = count($imageData);
    // INSERT ALL THE IMAGE OF CLASSIFIED
    foreach( $imageData as $images ) {
      $photoData = array();
      $photoData['album_id'] = $album_id;
      $photoData['classified_id'] = $data['listing_id'];
      $photoData['user_id'] = $data['user_id'];
      $photoData['creation_date'] = $this->_translateTime($data['time_stamp']);
      $photoData['modified_date'] = $this->_translateTime($data['time_stamp']);
      $photoData['collection_id'] = $album_id;
      $phDest = $this->getStringBetween($images['image_path'], '{', '}');
      if( !empty($phDest) && count($phDest) > 0 )
        $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
      else {
        $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/marketplace' . DIRECTORY_SEPARATOR . $images['image_path'];
      }
      $des = explode('%s', $srcPath);
      $file = $des[0];
      if( isset($des[1]) )
        $file = $des[0] . $des[1];
      if( $file ) {
        //CREATE THE FILE FOR CLASSIFIED PHOTO
        try {
          $fileInfo = array(
            'parent_type' => 'classified',
            'parent_id' => $data['listing_id'],
            'user_id' => @$data['user_id'],
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
          $this->_logFile("Marketplace photo not found . Photo path " . $file);
          continue;
        }
        if( $file_id ) {
          $photoData['file_id'] = $file_id;
          $newData['photo_id'] = $file_id;
        }
      }

      $this->getToDb()->insert('engine4_classified_photos', $photoData);
    }
    //UPDATE ALBUM COLLECTIBLE COUNT
    $this->getToDb()->update('engine4_classified_albums', array(
      'collectible_count' => $count,
      ), array(
      'album_id = ?' => $album_id,
      'classified_id = ?' => $data['listing_id'],
    ));

    //PRIVACY
    try {
      //set privacy
      $classifiedPrivacy = $this->_translateClassifiedPrivacy($data['privacy']);
      $newData['view_privacy'] = $classifiedPrivacy[0];

      $this->_insertPrivacy('classified', $data['listing_id'], 'view', $this->_translateClassifiedPrivacy($data['privacy']));
      $this->_insertPrivacy('classified', $data['listing_id'], 'comment', $this->_translateClassifiedPrivacy($data['privacy_comment']));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $data['listing_id'] . ' : ' . $e->getMessage());
    }

    //SEARCH
    if( @$newData['search'] ) {
      $this->_insertSearch('classified', @$newData['classified_id'], @$newData['title'], @$newData['body']);
    }

    return $newData;
  }
}

/*
CREATE TABLE IF NOT EXISTS `phpfox_marketplace` (
  `listing_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `view_id` tinyint(3) NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `currency_id` char(3) NOT NULL DEFAULT 'USD',
  `price` decimal(14,2) NOT NULL DEFAULT '0.00',
  `country_iso` char(2) DEFAULT NULL,
  `country_child_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `postal_code` varchar(20) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `image_path` varchar(75) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `is_sell` tinyint(1) NOT NULL DEFAULT '0',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `auto_sell` tinyint(1) NOT NULL DEFAULT '0',
  `mini_description` varchar(255) DEFAULT NULL,
  `is_notified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`listing_id`),
  KEY `user_id` (`user_id`),
  KEY `view_id` (`view_id`,`privacy`),
  KEY `view_id_2` (`view_id`,`privacy`,`is_featured`),
  KEY `listing_id` (`listing_id`,`view_id`),
  KEY `is_notified` (`is_notified`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */


/*
CREATE TABLE IF NOT EXISTS `phpfox_marketplace_image` (
  `image_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `listing_id` int(10) unsigned NOT NULL,
  `image_path` varchar(50) NOT NULL,
  `server_id` tinyint(1) NOT NULL,
  `ordering` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`image_id`),
  KEY `listing_id` (`listing_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
CREATE TABLE IF NOT EXISTS `engine4_classified_classifieds` (
  `classified_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL,
  `photo_id` int(10) unsigned NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`classified_id`),
  KEY `owner_id` (`owner_id`),
  KEY `search` (`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 CREATE TABLE IF NOT EXISTS `engine4_classified_albums` (
  `album_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `classified_id` int(11) unsigned NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `collectible_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`album_id`),
  KEY `classified_id` (`classified_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 CREATE TABLE IF NOT EXISTS `engine4_classified_photos` (
  `photo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `classified_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `collection_id` int(11) unsigned NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`),
  KEY `classified_id` (`classified_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
