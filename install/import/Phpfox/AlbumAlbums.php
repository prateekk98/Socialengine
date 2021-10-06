<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AlbumAlbums.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_AlbumAlbums extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('group_id=?' => 0, 'profile_id=?' => 0);
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'photo_album';
    $this->_toTable = 'engine4_album_albums';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET DESCRIPTION
    $description = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'photo_album_info', 'description')
      ->where('album_id = ?', $data['album_id'])
      ->query()
      ->fetchColumn();

    //MAKING ALBUM ARRAY FOR INSERTION
    $newData = array();
    $newData['album_id'] = $data['album_id'];
    $newData['title'] = $data['name'];
    $newData['description'] = !$description ? $data['name'] : $description;
    $newData['owner_type'] = 'user';
    $newData['owner_id'] = $data['user_id'];
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['search'] = 1;
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    if( is_null($data['time_stamp_update']) || $data['time_stamp_update'] == 0 )
      $newData['modified_date'] = $this->_translateTime($data['time_stamp_update']);
    $newData['comment_count'] = $data['total_comment'];

    if( $this->_columnExist($this->getToTable(), 'photos_count') )
      $newData['photos_count'] = $data['total_photo'];
    if( $this->_columnExist($this->getToTable(), 'like_count') )
      $newData['like_count'] = $data['total_like'];

    //SET TYPE
    $newData['type'] = ($newData['title'] == 'Profile Pictures') ? 'profile' : null;

    //SET PRIVACY
    $albumPrivacy = $this->_translateAlbumPrivacy($data['privacy']);
    $newData['view_privacy'] = $albumPrivacy[0];

    //PRIVACY
    $this->_insertPrivacy('album', $data['album_id'], 'view', $this->_translateAlbumPrivacy($data['privacy']));
    $this->_insertPrivacy('album', $data['album_id'], 'comment', $this->_translateAlbumPrivacy($data['privacy_comment']));
    $this->_insertPrivacy('album', $data['album_id'], 'tag', $this->_translateAlbumPrivacy(0));

    //SEARCH
    if( @$newData['search'] ) {
      $this->_insertSearch('album', @$newData['album_id'], @$newData['title'], @$newData['description']);
    }

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_album` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `module_id` varchar(75) DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL,
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `time_stamp_update` int(10) unsigned NOT NULL DEFAULT '0',
  `total_photo` int(10) unsigned NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `profile_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`album_id`),
  KEY `user_id` (`user_id`),
  KEY `view_id` (`view_id`,`group_id`,`user_id`),
  KEY `album_id` (`album_id`,`view_id`,`privacy`),
  KEY `view_id_2` (`view_id`,`group_id`,`privacy`),
  KEY `view_id_3` (`view_id`,`privacy`,`user_id`),
  KEY `view_id_4` (`view_id`,`privacy`,`total_photo`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_album_info` (
  `album_id` int(10) unsigned NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  UNIQUE KEY `album_id` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_category_data` (
  `photo_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `photo_id` (`photo_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
CREATE TABLE IF NOT EXISTS `engine4_album_albums` (
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
  `type` varchar(64) CHARACTER SET latin1 COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`album_id`),
  KEY `owner_type` (`owner_type`,`owner_id`),
  KEY `category_id` (`category_id`),
  KEY `search` (`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
