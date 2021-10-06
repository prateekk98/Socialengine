<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractActivityComments.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractActivityComments extends Install_Import_Phpfox_Abstract
{
  protected $_toTable = '';
  protected $_fromResourceType;
  protected $_toResourceType;
  protected $_priority = 90;

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_fromResourceType', '_toResourceType'
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
    $this->_toTable = 'engine4_activity_comments';
    $this->_fromTable = $this->getFromPrefix() . 'comment';
    $this->_fromOrderBy = array(array('comment_id'), 'ASC');
  }

  /**
   * @param array $data
   * @param null $key
   * @return void
   */
  protected function _translateRow(array $data, $key = null)
  {
    $toType = $this->getToResourceType();
    $feedType = $toType;
    if( $toType == 'pages' ) {
      $feedType = 'pages_comment';
    }

    $count = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'comment', 'count(*)')
      ->where('item_id=?', $data['item_id'])
      ->where('type_id=?', $toType)
      ->query()
      ->fetchColumn();

    $data['item_id'] = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'feed', 'feed_id')
      ->where('type_id = ?', $feedType)
      ->where('item_id = ?', $data['item_id'])
      ->query()
      ->fetchColumn();

    $body = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'comment_text', 'text')
      ->where('comment_id= ?', $data['comment_id'])
      ->query()
      ->fetchColumn();

    $parentCommentId = 0;
    if( $data['parent_id'] > 0 ) {
      $parentCommentId = $this->getCommentMap('activity', $data['parent_id']);
    }

    $newData = array(
      'resource_id' => $data['item_id'],
      'poster_type' => 'user',
      'poster_id' => $data['user_id'],
      'body' => $body,
      'creation_date' => $this->_translateTime($data['time_stamp']),
      'like_count' => $data['total_like']
    );

    if( $this->_columnExist('engine4_activity_comments', 'parent_comment_id') ) {
      $newData['parent_comment_id'] = $parentCommentId;
    }

    $this->getToDb()->insert('engine4_activity_comments', $newData);

    $comment_id = $this->getToDb()->lastInsertId();
    $this->setCommentMap('activity', $data['comment_id'], $comment_id);

    $likes = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'like', '*')
      ->where('item_id= ?', $data['comment_id'])
      ->where('type_id= ?', 'feed_mini')
      ->query()
      ->fetchAll();

    foreach( $likes as $like ) {
      $newLikeData = array(
        'resource_type' => 'activity_comment',
        'resource_id' => $comment_id,
        'poster_type' => 'user',
        'poster_id' => $like['user_id']
      );

      $this->getToDb()->insert('engine4_core_likes', $newLikeData);
    }

    $isNestedCommentPlugin = $this->isPluginExist('nestedcomment');
    if( $isNestedCommentPlugin ) {
      $dislikes = $this->getFromDb()->select()
        ->from($this->getFromPrefix() . 'action', '*')
        ->where('item_id= ?', $data['comment_id'])
        ->where('item_type_id = ?', 'comment')
        ->query()
        ->fetchAll();

      foreach( $dislikes as $dislike ) {
        $newDisLikeData = array(
          'resource_type' => 'activity_comment',
          'resource_id' => $comment_id,
          'poster_type' => 'user',
          'poster_id' => $dislike['user_id'],
          'creation_date' => $this->_translateTime($dislike['time_stamp'])
        );

        $this->getToDb()->insert('engine4_nestedcomment_dislikes', $newDisLikeData);
      }
    }

    $this->getToDb()->update('engine4_activity_actions', array(
      'comment_count' => $count,
    ), array(
      'action_id = ?' => $data['item_id'],
    ));
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_comment` (
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
 *
CREATE TABLE IF NOT EXISTS `phpfox_feed` (
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `type_id` varchar(75) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `feed_reference` int(10) NOT NULL DEFAULT '0',
  `parent_feed_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_module_id` varchar(75) DEFAULT NULL,
  `time_update` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`feed_id`),
  KEY `privacy_2` (`privacy`,`time_stamp`,`feed_reference`),
  KEY `privacy_3` (`privacy`,`user_id`,`feed_reference`),
  KEY `privacy_4` (`privacy`,`parent_user_id`,`feed_reference`),
  KEY `type_id` (`type_id`,`item_id`,`feed_reference`),
  KEY `privacy` (`privacy`,`user_id`,`time_stamp`,`feed_reference`),
  KEY `time_stamp` (`time_stamp`,`feed_reference`),
  KEY `time_update` (`time_update`),
  KEY `privacy_5` (`privacy`,`parent_user_id`),
  KEY `user_id` (`user_id`,`feed_reference`,`time_stamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_comment_text` (
  `comment_id` int(10) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext,
  KEY `comment_id` (`comment_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
