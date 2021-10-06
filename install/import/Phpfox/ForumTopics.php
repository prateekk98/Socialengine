<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    ForumTopics.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_ForumTopics extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_priority = 93;
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'forum_thread';
    $this->_toTable = 'engine4_forum_topics';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //PREPARING ARRAY FOR FORUM TOPIC
    $newData = array();

    $newData['topic_id'] = $data['thread_id'];
    if( $data['forum_id'] )
      $newData['forum_id'] = $data['forum_id'];
    $newData['user_id'] = $data['user_id'];
    $newData['title'] = $data['title'];
    $newData['closed'] = $data['is_closed'];
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_update']);
    $newData['sticky'] = $data['order_id'];
    $newData['lastposter_id'] = $data['last_user_id'] ? $data['last_user_id'] : $data['user_id'];
    $newData['view_count'] = $data['total_view'];
    $type_id = '';
    //FIND WHEATER THIS FORUM IS PAGES FORUM AND FIND PAGES TYPE
    if( !$data['forum_id'] ) {
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'pages', 'type_id')
        ->where('page_id = ?', $data['group_id'])
        ->query()
        ->fetchColumn();
    }
    $albumTitle = 'Forum Photos';
    $isGroupForum = false;
    if( !empty($type_id) ) {

      $newData['group_id'] = $data['group_id'];
      $albumTitle = 'Group Photos';
      $isGroupForum = true;
      //INSERT GROUP FORUM TOPIC
      $this->getToDb()->insert('engine4_group_topics', $newData);
    } else {
      //INSERT THE FORUM TOPIC
      $this->getToDb()->insert('engine4_forum_topics', $newData);
    }

    $topic_id = $this->getToDb()->lastInsertId();
    //FIND ALL THE POST OF THIS TOPIC
    $posts = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'forum_post', '*')
      ->where('thread_id = ?', $data['thread_id'])
      ->query()
      ->fetchAll();
    $newPostData = array();
    $count = 0;
    //INSERT ALL THE POST OF TOPIC
    foreach( $posts as $post ) {
      //GETTING TEXT
      $text = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'forum_post_text', 'text')
        ->where('post_id = ?', $post['post_id'])
        ->query()
        ->fetchColumn();

      if( is_null($text) || $text === false )
        $text = '';

      $userInfo = array(
        'user_id' => $post['user_id'],
        'time_stamp' => $post['time_stamp'],
        'text' => $text,
        'item_id' => $post['post_id'],
        'album_title' => $albumTitle,
        'album_type' => 'forum',
        'categoryId' => 'forum'
      );
      if( !empty($type_id) ) {
        $userInfo['page_id'] = $data['group_id'];
        $userInfo['album_table'] = 'group_albums';
      }
      //FIND THE POST BODY
      $body = $this->getBody($userInfo);
      if( is_null($body) )
        $body = '';

      $newPostData['post_id'] = $post['post_id'];
      $newPostData['topic_id'] = $data['thread_id'];
      if( $data['forum_id'] )
        $newPostData['forum_id'] = $data['forum_id'];
      $newPostData['user_id'] = $post['user_id'];
      $newPostData['body'] = $body;
      $newPostData['creation_date'] = $this->_translateTime($post['time_stamp']);
      $newPostData['modified_date'] = $post['update_time'] ? $this->_translateTime($post['update_time']) : $this->_translateTime($post['time_stamp']);
//            INSERT THE FORUM POST
      if( $isGroupForum ) {
        $newPostData['group_id'] = $data['group_id'];
        $this->getToDb()->insert('engine4_group_posts', $newPostData);
      } else {
        $this->getToDb()->insert('engine4_forum_posts', $newPostData);
      }
      $post_id = $this->getToDb()->lastInsertId();
      //UPDATE THE COUNT OF POST INTO THE TOPIC
      if( $isGroupForum ) {
        $this->getToDb()->update('engine4_group_topics', array(
          'lastpost_id' => $post_id,
          'post_count' => ++$count
          ), array(
          'topic_id = ?' => $topic_id
        ));
      } else {
        $this->getToDb()->update('engine4_forum_topics', array(
          'lastpost_id' => $post_id,
          'post_count' => ++$count
          ), array(
          'topic_id = ?' => $topic_id,
          'forum_id = ?' => $data['forum_id'],
        ));
      }
    }
    //PREPARE AN ARRAY FOR TOPIC WATCHES
    $newWatchData = array();
    if( $data['forum_id'] )
      $newWatchData['resource_id'] = $data['forum_id'];

    $newWatchData['topic_id'] = $data['thread_id'];
    $newWatchData['user_id'] = $data['user_id'];
    $newWatchData['watch'] = 1;
    if( $isGroupForum ) {
      $newWatchData['resource_id'] = $data['group_id'];
      $this->getToDb()->insert('engine4_group_topicwatches', $newWatchData);
    } else {
      $this->getToDb()->insert('engine4_forum_topicwatches', $newWatchData);
    }

    if( $data['forum_id'] ) {
      //PREPARE AN ARRAY FOR TOPIC VIEW
      $newTopicViewsData = array();
      $newTopicViewsData['user_id'] = $data['user_id'];
      $newTopicViewsData['topic_id'] = $data['thread_id'];
      $newTopicViewsData['last_view_date'] = $this->_translateTime($data['time_stamp']);
      $this->getToDb()->insert('engine4_forum_topicviews', $newTopicViewsData);
      //FIND THE FORUM DETAIL
      $forums = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'forum', '*')
        ->where('forum_id = ?', $data['forum_id'])
        ->where('thread_id = ?', $data['thread_id'])
        ->query()
        ->fetchAll();
      //UPDATE THE POST COUNT INTO THE FORUM
      foreach( $forums as $forum ) {
        $this->getToDb()->update('engine4_forum_forums', array(
          'topic_count' => $forum['total_thread'],
          'post_count' => $forum['total_post'] + 1,
          'lastpost_id' => $post_id,
          'lastposter_id' => $forum['last_user_id'],
          ), array(
          'forum_id = ?' => $data['forum_id'],
        ));
      }
    }
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_forum` (
  `forum_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `is_category` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `name_url` varchar(255) NOT NULL,
  `description` mediumtext,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `total_post` int(10) unsigned NOT NULL DEFAULT '0',
  `total_thread` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  KEY `view_id` (`view_id`),
  KEY `post_id` (`post_id`),
  KEY `thread_id` (`thread_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_forum_thread` (
  `thread_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` smallint(4) unsigned NOT NULL DEFAULT '0',
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `poll_id` int(10) unsigned NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `start_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_announcement` tinyint(1) NOT NULL DEFAULT '0',
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `title_url` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `time_update` int(10) unsigned NOT NULL DEFAULT '0',
  `order_id` tinyint(1) NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `total_post` int(10) unsigned NOT NULL DEFAULT '0',
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`thread_id`),
  KEY `forum_id` (`forum_id`,`group_id`,`view_id`),
  KEY `group_id` (`group_id`,`view_id`,`title_url`),
  KEY `forum_id_2` (`forum_id`),
  KEY `group_id_2` (`group_id`,`view_id`,`is_announcement`),
  KEY `group_id_3` (`group_id`,`title_url`),
  KEY `view_id` (`view_id`),
  KEY `thread_id` (`thread_id`,`group_id`),
  KEY `start_id` (`start_id`),
  KEY `view_id_2` (`view_id`,`title`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */
/*
 * CREATE TABLE IF NOT EXISTS `phpfox_forum_post` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` int(10) unsigned NOT NULL,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `total_attachment` int(11) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_user` varchar(100) DEFAULT NULL,
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`),
  KEY `thread_id` (`thread_id`),
  KEY `user_id` (`user_id`),
  KEY `thread_id_2` (`thread_id`,`view_id`),
  KEY `view_id` (`view_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_forum_post_text` (
  `post_id` int(11) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext,
  UNIQUE KEY `post_id` (`post_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_topics` (
  `topic_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `forum_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `sticky` tinyint(4) NOT NULL DEFAULT '0',
  `closed` tinyint(4) NOT NULL DEFAULT '0',
  `post_count` int(11) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `lastpost_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lastposter_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`topic_id`),
  KEY `forum_id` (`forum_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_topicviews` (
  `user_id` int(11) unsigned NOT NULL,
  `topic_id` int(11) unsigned NOT NULL,
  `last_view_date` datetime NOT NULL,
  PRIMARY KEY (`user_id`,`topic_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_topicwatches` (
  `resource_id` int(10) unsigned NOT NULL,
  `topic_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `watch` tinyint(1) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`resource_id`,`topic_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
