<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    EventStatusFeed.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_EventStatusFeed extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_isTableExist = array('engine4_event_events');
  // protected $_fromWhere = array('type_id=?' => 'event_comment');
  protected $_priority = 9000;
  protected $_toTableTruncate = false;
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'event_feed';
    $this->_toTable = 'engine4_activity_actions';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {
    $objectType = 'event';
    $resourceType = "post";
    //CHECK TYPE ID AND GET BODY
    if( $data['type_id'] == 'photo' ) {
      $body = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo_info', 'description')
        ->where('photo_id = ?', $data['item_id'])
        ->query()
        ->fetchColumn();
    } else {
      $body = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'event_feed_comment', 'content')
        ->where('feed_comment_id = ?', $data['item_id'])
        ->where('user_id = ?', $data['user_id'])
        ->where('parent_user_id = ?', $data['parent_user_id'])
        ->query()
        ->fetchColumn();
    }

    $body = $body ? $body : '';
    //MAKING DATA ARRAY
    $newData = array(
      'type' => $resourceType,
      'subject_type' => 'user',
      'subject_id' => $data['user_id'],
      'object_type' => $objectType,
      'object_id' => $data['parent_user_id'],
      'body' => $body,
      'params' => '',
      'date' => $this->_translateTime($data['time_stamp']),
      'attachment_count' => 1
    );
    $privacy_field = $this->getToDb()->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'privacy'")->fetch();
    $privacy = 'everyone';
    if( !empty($privacy_field) ) {
      switch( $data['privacy'] ) {
        case '1':
        case '2':
          $privacy = 'friends';
          break;
        case '3':
          $privacy = 'onlyme';
          break;
        default: $privacy = 'everyone';
      }
      $newData = array_merge($newData, array('privacy' => $privacy));
    }
    //INSERT INTO ACTIVITY
    $this->getToDb()->insert('engine4_activity_actions', $newData);
    $feed_id = $this->getToDb()->lastInsertId();
    $targetTypes = array();
    switch( $privacy ) {
      case 'friends':
        $targetTypes['owner'] = $data['user_id'];
        $targetTypes['parent'] = $data['user_id'];
        $targetTypes['members'] = $data['user_id'];
        break;
      case 'onlyme':
        $targetTypes['owner'] = $data['user_id'];
        $targetTypes['parent'] = $data['user_id'];
        break;
      case 'everyone':
        $targetTypes['everyone'] = 0;
        $targetTypes['registered'] = 0;
        $targetTypes['owner'] = $data['user_id'];
        $targetTypes['parent'] = $data['user_id'];
        $targetTypes['members'] = $data['user_id'];
        break;
    }
    //INSERT INTO ACTIVITY STREAM
    foreach( $targetTypes as $targetType => $targetIdentity ) {
      try {
        $this->getToDb()->insert('engine4_activity_stream', array(
          'target_type' => $targetType,
          'target_id' => $targetIdentity,
          'subject_type' => 'user',
          'subject_id' => $data['user_id'],
          'object_type' => $objectType,
          'object_id' => $data['parent_user_id'],
          'type' => $resourceType,
          //   'date' => $this->_translateTime($data['time_stamp']),
          'action_id' => $feed_id,
        ));
      } catch( Exception $e ) {
        $this->_error('Problem adding activity privacy: ' . $e->getMessage());
      }
    }
    //FIND RESOURCE TYPE 
    $type = $data['type_id'];
    if( $data['type_id'] == 'photo' ) {
      $type = 'album_photo';
    } elseif( $data['type_id'] == 'link' ) {
      $type = 'core_link';
    }

    //GET LIKES
    $likes = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'like', '*')
      ->where('item_id = ?', $data['item_id'])
      ->where('type_id = ?', $data['type_id'])
      ->query()
      ->fetchAll();
    $i = 0;
    //INSERT LIKE
    foreach( $likes as $likes ) {

      if( $data['type_id'] == 'link' || $data['type_id'] == 'pages_comment' || $data['type_id'] == 'event_comment' ) {
        $this->getToDb()->insert('engine4_activity_likes', array(
          'resource_id' => $feed_id,
          'poster_type' => 'user',
          'poster_id' => $likes['user_id'],
        ));
      } else {
        $this->getToDb()->insert('engine4_core_likes', array(
          'resource_type' => $type,
          'resource_id' => $data['item_id'],
          'poster_type' => 'user',
          'poster_id' => $likes['user_id']
        ));
      }
      ++$i;
    }

    if( $data['type_id'] == 'event_comment' ) {
      $data['type_id'] = 'event';
    }

    //GET COMMENTS
    $comments = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'comment', '*')
      ->where('item_id = ?', $data['item_id'])
      ->where('type_id = ?', $data['type_id'])
      ->query()
      ->fetchAll();
    foreach( $comments as $comment ) {
      //FIND OWNER USER ID
      $userId = $this->getFromDb()->query("SELECT  " . $this->getfromPrefix() . "pages.user_id FROM " . $this->getfromPrefix() . "user left join  " . $this->getfromPrefix() . "pages on page_id=profile_page_id where profile_page_id<>0 and  " . $this->getfromPrefix() . "user.user_id=" . $comment['user_id'])->fetchColumn(0);
      if( $userId )
        $comment['user_id'] = $userId;
      //FIND ALL THE LIKE OF THIS COMMENT
      $likeRows = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'like', '*')
        ->where('item_id = ?', $comment['comment_id'])
        ->where('type_id = ?', 'feed_mini')
        ->query()
        ->fetchAll();
      //FIND THE TEXT OF THIS COMMENT
      $body = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'comment_text', 'text')
        ->where('comment_id= ?', $comment['comment_id'])
        ->query()
        ->fetchColumn();
      $parentCommentId = 0;
      if( $data['type_id'] == 'link' || $data['type_id'] == 'event' ) {
        //FIND PARENT COMMENT ID
        if( $comment['parent_id'] > 0 )
          $parentCommentId = $this->getCommentMap('activity', $comment['parent_id']);
        $likeResourceType = "activity_comment";
        //PREPARING AN ARRAY TO INSERT THE DATA INTO ACTIVITY COMMENT
        $commentData = array(
          'resource_id' => $feed_id,
          'poster_type' => 'user',
          'poster_id' => $comment['user_id'],
          'body' => $body,
          'creation_date' => $this->_translateTime($comment['time_stamp']),
          'like_count' => count($likeRows)
        );
        //CHECKING FOR PARENT COMMENT ID COLUMN EXIST OR NOT
        if( $this->_columnExist('engine4_activity_comments', 'parent_comment_id') )
          $commentData['parent_comment_id'] = $parentCommentId;

        //INSERT INTO ACTIVITY COMMENT
        $this->getToDb()->insert('engine4_activity_comments', $commentData);

        $comment_id = $this->getToDb()->lastInsertId();
        //MAP THE COMMENT
        $this->setCommentMap('activity', $comment['comment_id'], $comment_id);
      }
      else {
        //FIND PARENT COMMENT ID
        if( $comment['parent_id'] > 0 )
          $parentCommentId = $this->getCommentMap('core', $comment['parent_id']);
        $likeResourceType = "core_comment";
        //PREPARING AN ARRAY FOR INSERTION OF CORE COMMENT
        $commentData = array(
          'resource_type' => $type,
          'resource_id' => $data['item_id'],
          'poster_type' => 'user',
          'poster_id' => $comment['user_id'],
          'body' => $body,
          'creation_date' => $this->_translateTime($comment['time_stamp']),
          'like_count' => count($likeRows),
        );
        //CHECKING PARENT COMMENT ID COLUMN EXIST OR NOT
        if( $this->_columnExist('engine4_core_comments', 'parent_comment_id') )
          $commentData['parent_comment_id'] = $parentCommentId;

        $this->getToDb()->insert('engine4_core_comments', $commentData);
        $comment_id = $this->getToDb()->lastInsertId();
        //MAP THE COMMENT
        $this->setCommentMap('core', $comment['comment_id'], $comment_id);
      }
      //INSERT ALL THE LIKE OF THIS COMMENT
      foreach( $likeRows as $row ) {

        $userId = $this->getFromDb()->query("SELECT  " . $this->getfromPrefix() . "pages.user_id FROM " . $this->getfromPrefix() . "user left join  " . $this->getfromPrefix() . "pages on page_id=profile_page_id where profile_page_id<>0 and  " . $this->getfromPrefix() . "user.user_id=" . $row['user_id'])->fetchColumn(0);
        if( $userId )
          $row['user_id'] = $userId;
        //INSERT LIKES
        $this->getToDb()->insert('engine4_core_likes', array(
          'resource_type' => $likeResourceType,
          'resource_id' => $comment_id,
          'poster_type' => 'user',
          'poster_id' => $row['user_id']
        ));
      }
    }

    //UPDATE LIKE AND COMMENT COUNT
    $this->getToDb()->update('engine4_activity_actions', array(
      'comment_count' => count($comments),
      'like_count' => $i
    ), array(
      'action_id = ?' => $feed_id,
    ));

    //INSERT ATTACHMENTS
    if( $data['type_id'] == 'photo' || $data['type_id'] == 'link' || $data['type_id'] == 'video' ) {
      $newAttachmentData = array(
        'action_id' => $feed_id,
        'type' => $type,
        'id' => $data['item_id'],
        'mode' => 1
      );
      $this->getToDb()->insert('engine4_activity_attachments', $newAttachmentData);
    }
    return false;
  }

}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_like` (
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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
 *
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_info` (
  `photo_id` int(10) unsigned NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT '0',
  `mime_type` varchar(150) DEFAULT NULL,
  `extension` varchar(20) NOT NULL,
  `description` mediumtext,
  `width` smallint(4) unsigned NOT NULL DEFAULT '0',
  `height` smallint(4) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_event_feed_comment` (
  `feed_comment_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(3) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(3) NOT NULL DEFAULT '0',
  `content` mediumtext,
  `time_stamp` int(10) unsigned NOT NULL,
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`feed_comment_id`),
  KEY `parent_user_id` (`parent_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */


/*
CREATE TABLE IF NOT EXISTS `phpfox_comment` (
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
CREATE TABLE IF NOT EXISTS `phpfox_comment_text` (
  `comment_id` int(10) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext,
  KEY `comment_id` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
CREATE TABLE IF NOT EXISTS `engine4_core_comments` (
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 *
 */


/*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_actions` (
  `action_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `params` text COLLATE utf8_unicode_ci,
  `date` datetime NOT NULL,
  `attachment_count` smallint(3) unsigned NOT NULL DEFAULT '0',
  `comment_count` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `like_count` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `privacy` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commentable` tinyint(1) NOT NULL DEFAULT '1',
  `shareable` tinyint(1) NOT NULL DEFAULT '1',
  `user_agent` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`action_id`),
  KEY `SUBJECT` (`subject_type`,`subject_id`),
  KEY `OBJECT` (`object_type`,`object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_stream` (
  `target_type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `target_id` int(11) unsigned NOT NULL,
  `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `action_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`target_type`,`target_id`,`action_id`),
  KEY `SUBJECT` (`subject_type`,`subject_id`,`action_id`),
  KEY `OBJECT` (`object_type`,`object_id`,`action_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */

/*
 *
CREATE TABLE IF NOT EXISTS `engine4_activity_likes` (
  `like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `resource_id` (`resource_id`),
  KEY `poster_type` (`poster_type`,`poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_attachments` (
  `attachment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `action_id` int(11) unsigned NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `mode` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`attachment_id`),
  KEY `action_id` (`action_id`),
  KEY `type_id` (`type`,`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_comments` (
  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `like_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`comment_id`),
  KEY `resource_type` (`resource_id`),
  KEY `poster_type` (`poster_type`,`poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
