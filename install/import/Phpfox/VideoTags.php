<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    VideoTags.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_VideoTags extends Install_Import_Phpfox_AbstractTags
{

  protected $_fromResourceType = 'video';
  protected $_toResourceType = 'video';
  protected $_isTableExist = array('engine4_video_videos');
  protected $_fromWhere = array('category_id=?' => 'video');
  protected $_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');

}

/*
 CREATE TABLE IF NOT EXISTS `phpfox_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `category_id` varchar(75) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `tag_text` varchar(255) NOT NULL,
  `tag_url` varchar(255) NOT NULL,
  `added` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `user_id` (`user_id`,`tag_text`),
  KEY `item_id` (`item_id`,`category_id`),
  KEY `category_id` (`category_id`),
  KEY `tag_url` (`tag_url`),
  KEY `user_search` (`category_id`,`user_id`,`tag_text`),
  KEY `user_search_general` (`category_id`,`user_id`),
  KEY `item_id_2` (`item_id`,`category_id`,`user_id`),
  KEY `item_id_3` (`item_id`,`category_id`,`tag_url`),
  KEY `category_id_2` (`category_id`,`tag_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_tags` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `text` (`text`)
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
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
