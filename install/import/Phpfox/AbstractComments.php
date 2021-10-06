<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractComments.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractComments extends Install_Import_Phpfox_Abstract
{

  protected $_toTable = '';
  protected $_fromResourceType;
  protected $_toResourceType;
  protected $_priority = 90;

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_fromResourceType', '_toResourceType', '_isTableExist'
    ));
  }

  public function getFromResourceType()
  {
    if( null === $this->_fromResourceType ) {
      throw new Engine_Exception('No resource type');
    }
    return $this->_fromResourceType;
  }

  public function getToResourceType()
  {
    if( null === $this->_toResourceType ) {
      throw new Engine_Exception('No resource type');
    }
    return $this->_toResourceType;
  }

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'comment';
    $this->_toTable = 'engine4_core_comments';
    $this->_fromOrderBy = array(array('comment_id'), 'ASC');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET RESOURCE TYPE
    $toType = $this->getToResourceType();
    $posterType = 'user';
    $resourceId = $data['item_id'];
    $posterId = $data['user_id'];
    //CHECK FOR GROUP [WE ARE SKIPPING THE COMMENTS FOR GROUP FOR BLOG, VIDEO]
    //FIND POSTER TYPE AND POSTER ID 
    if( $toType == 'blog' ) {
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'blog', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'blog.item_id', array('type_id', 'page_id'))
        ->where($this->getfromPrefix() . 'blog.blog_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'blog.module_id = ?', 'pages')
        ->query()
        ->fetch();

      if( isset($pageInfo['type_id']) )
        return false;
    } else if( $toType == 'video' ) {
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'video', array($this->getfromPrefix() . 'video.module_id'))
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'video.item_id', array('type_id', 'page_id'))
        ->where($this->getfromPrefix() . 'video.video_id = ?', $data['item_id'])
        ->query()
        ->fetch();
      if( $pageInfo ) {
        return false;
      }
    } else if( $toType == 'music_playlist_song' ) {
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_song', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'music_song.item_id', 'type_id')
        ->where($this->getfromPrefix() . 'music_song.song_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'music_song.module_id = ?', 'pages')
        ->query()
        ->fetchColumn();
      if( $type_id == 3 )
        return false;
    } else if( $toType == 'music_playlist' ) {
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_album', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'music_album.item_id', array('type_id', 'page_id'))
        ->where($this->getfromPrefix() . 'music_album.album_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'music_album.module_id = ?', 'pages')
        ->query()
        ->fetch();
      if( isset($pageInfo['type_id']) )
        return false;
    } else if( $toType == 'album_photo' ) {

      //GET Group id
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo', array($this->getfromPrefix() . 'photo.module_id'))
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'photo.group_id', array($this->getfromPrefix() . 'pages.type_id', 'page_id'))
        ->where('photo_id= ?', $data['item_id'])
        ->query()
        ->fetch();
      if( $pageInfo ) {
        $toType = 'group_photo';
        if( $pageInfo['module_id'] == 'event' )
          return false;
      }
    }

    //GET BODY
    $body = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'comment_text', 'text')
      ->where('comment_id= ?', $data['comment_id'])
      ->query()
      ->fetchColumn();
    $parentCommentId = 0;
    if( $data['parent_id'] > 0 )
      $parentCommentId = $this->getCommentMap('core', $data['parent_id']);
    //Preparing Array For Comment 
    $commentData = array(
      'resource_type' => $toType,
      'resource_id' => $resourceId,
      'poster_type' => $posterType,
      'poster_id' => $posterId,
      'body' => $body,
      'creation_date' => $this->_translateTime($data['time_stamp']),
      'like_count' => $data['total_like']
    );
    if( $this->_columnExist('engine4_core_comments', 'parent_comment_id') )
      $commentData['parent_comment_id'] = $parentCommentId;
    //INSERT COMMENTS
    $this->getToDb()->insert('engine4_core_comments', $commentData);

    //GET LAST COMMENT INSERT ID
    $comment_id = $this->getToDb()->lastInsertId();
    $this->setCommentMap('core', $data['comment_id'], $comment_id);
    //GET LIKE ROWS
    $rows = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'like', '*')
      ->where('item_id = ?', $data['comment_id'])
      ->where('type_id = ?', 'feed_mini')
      ->query()
      ->fetchAll();
    foreach( $rows as $row ) {
      //INSERT LIKES
      $this->getToDb()->insert('engine4_core_likes', array(
        'resource_type' => 'core_comment',
        'resource_id' => $comment_id,
        'poster_type' => 'user',
        'poster_id' => $row['user_id'],
      ));
    }

  }

}

/*
 * CREATE TABLE IF NOT EXISTS `'.$this->getfromPrefix().'comment` (
  `comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type_id` varchar(75) NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `owner_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_user` varchar(100) DEFAULT NULL,
  `rating` varchar(10) DEFAULT NULL,
  `ip_address` varchar(15) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `author_email` varchar(100) DEFAULT NULL,
  `author_url` varchar(255) DEFAULT NULL,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `child_total` smallint(4) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `user_id` (`user_id`,`view_id`),
  KEY `owner_user_id` (`owner_user_id`,`view_id`),
  KEY `type_id` (`type_id`,`item_id`,`view_id`),
  KEY `parent_id` (`parent_id`,`view_id`),
  KEY `parent_id_2` (`parent_id`,`type_id`,`item_id`,`view_id`),
  KEY `view_id` (`view_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `'.$this->getfromPrefix().'comment_text` (
  `comment_id` int(10) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext,
  KEY `comment_id` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `'.$this->getfromPrefix().'like` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` varchar(75) NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `type_id` (`type_id`,`item_id`),
  KEY `type_id_2` (`type_id`,`item_id`,`user_id`),
  KEY `type_id_3` (`type_id`,`user_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_comments` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `like_count` int(11) unsigned NOT NULL DEFAULT '0',
  `parent_comment_id` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `resource_type` (`resource_type`,`resource_id`),
  KEY `poster_type` (`poster_type`,`poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_likes` (
  `like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `resource_type` (`resource_type`,`resource_id`),
  KEY `poster_type` (`poster_type`,`poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
