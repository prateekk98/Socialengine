<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    ForumReplyFeeds.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_ForumReplyFeeds extends Install_Import_Phpfox_AbstractFeeds
{

  protected $_fromResourceType = 'forum_topic';
  protected $_toResourceType = 'forum_topic_reply';
  protected $_isTableExist = array('engine4_forum_topics');
  protected $_fromWhere = array('type_id=?' => 'forum_post');
  protected $_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');

}

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
 * /*
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
