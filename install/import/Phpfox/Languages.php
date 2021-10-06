<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    VideoVideos.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_Languages extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('language_id<>?' => 'en');
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'language';
    $this->_toTable = '';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    $translate = Zend_Registry::get('Zend_Translate');
    $localeCode = $data['language_code'];
    if( !in_array($localeCode, $translate->getList()) ) {
      $filename = APPLICATION_PATH . "/application/languages/$localeCode/custom.csv";
      mkdir(dirname($filename));
      chmod(dirname($filename), 0777);
      touch($filename);
      chmod($filename, 0777);
      $csv = new Engine_Translate_Writer_Csv($filename);
      // each language pack must have at least one line written to it to be recognized
      $csv->setTranslation($localeCode, $localeCode);
      $csv->write();
    }
    return false;
  }
}

/*
CREATE TABLE IF NOT EXISTS `phpfox_video` (
  `video_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `in_process` tinyint(1) NOT NULL DEFAULT '0',
  `is_stream` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_spotlight` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `destination` varchar(75) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `file_ext` varchar(10) DEFAULT NULL,
  `duration` varchar(8) DEFAULT NULL,
  `resolution_x` varchar(4) DEFAULT NULL,
  `resolution_y` varchar(4) DEFAULT NULL,
  `image_path` varchar(75) DEFAULT NULL,
  `image_server_id` tinyint(1) NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `total_score` decimal(4,2) NOT NULL DEFAULT '0.00',
  `total_rating` int(10) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL DEFAULT '0',
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `is_viewed` tinyint(1) NOT NULL DEFAULT '0',
  `custom_v_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`video_id`),
  KEY `in_process` (`in_process`),
  KEY `user_id` (`user_id`),
  KEY `view_id` (`view_id`),
  KEY `in_process_2` (`in_process`,`view_id`,`item_id`,`privacy`),
  KEY `in_process_3` (`in_process`,`view_id`,`item_id`,`user_id`),
  KEY `in_process_4` (`in_process`,`view_id`,`item_id`,`privacy`,`title`),
  KEY `in_process_5` (`in_process`,`view_id`,`item_id`,`privacy`,`user_id`),
  KEY `in_process_6` (`in_process`,`view_id`,`privacy`,`title`),
  KEY `custom_v_id` (`custom_v_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */


/*
CREATE TABLE IF NOT EXISTS `phpfox_video_category` (
  `category_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL DEFAULT '0',
  `used` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`,`is_active`),
  KEY `is_active` (`is_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_video_category_data` (
  `video_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `category_id` (`category_id`),
  KEY `video_id` (`video_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_video_videos` (
  `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `owner_type` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `parent_type` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL,
  `code` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `photo_id` int(11) unsigned DEFAULT NULL,
  `rating` float NOT NULL,
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `file_id` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `duration` int(9) unsigned NOT NULL,
  `rotation` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`video_id`),
  KEY `owner_id` (`owner_id`,`owner_type`),
  KEY `search` (`search`),
  KEY `creation_date` (`creation_date`),
  KEY `view_count` (`view_count`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */
