<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    Announcement.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_Announcement extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'announcement';
    $this->_toTable = 'engine4_announcement_announcements';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET SUBJECT
    $subject_var_array = explode('.', $data['subject_var']);
    $subject = $subject_var_array[1];

    //GET TITLE
    $title = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'language_phrase', 'text')
      ->where('var_name = ?', $subject)
      ->query()
      ->fetchColumn();

    //GET DESCRIPTION
    if( $data['content_var'] ) {
      $content_var_array = explode('.', $data['content_var']);
      $content = $content_var_array[1];
      $description = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'language_phrase', 'text')
        ->where('var_name = ?', $content)
        ->query()
        ->fetchColumn();
    }
    $allowedGrp = unserialize($data['user_group']);
    $levels = array();
    //FIND LEVEL TO WHICH ALLOWED TO SEE ANNOUNCEMENT
    if( count($allowedGrp) > 0 ) {
      $allowedGrpNames = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'user_group', 'title')
        ->where('user_group_id in ( ? ) ', $allowedGrp)
        ->query()
        ->fetchAll();
      foreach( $allowedGrpNames as $grp ) {
        $grpName = $grp['title'];
        $levels[] = $this->getLevelIdByTitleName($grpName);
      }
    }
    //PREPARE AN ARRAY TO INSERT AN ANNOUNCEMENT.
    $newData = array();
    $newData['announcement_id'] = $data['announcement_id'];
    $newData['title'] = $title;
    $newData['body'] = $description;
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['member_levels'] = json_encode($levels);
    $newData['profile_types'] = "1";
    $newData['user_id'] = $this->getSuperAdminUserId();
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_announcement` (
  `announcement_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `subject_var` varchar(255) DEFAULT NULL,
  `intro_var` varchar(255) DEFAULT NULL,
  `content_var` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `can_be_closed` tinyint(1) NOT NULL DEFAULT '1',
  `show_in_dashboard` tinyint(1) NOT NULL DEFAULT '1',
  `start_date` int(11) unsigned NOT NULL,
  `location` tinyint(2) NOT NULL DEFAULT '6',
  `country_iso` char(2) NOT NULL DEFAULT '0',
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `age_from` tinyint(2) NOT NULL DEFAULT '0',
  `age_to` tinyint(2) NOT NULL DEFAULT '0',
  `user_group` varchar(255) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `gmt_offset` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`announcement_id`),
  KEY `is_active` (`is_active`,`show_in_dashboard`),
  KEY `is_active_2` (`is_active`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_language_phrase` (
  `phrase_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `language_id` varchar(12) NOT NULL,
  `module_id` varchar(75) DEFAULT NULL,
  `product_id` varchar(25) NOT NULL DEFAULT 'phpfox',
  `version_id` varchar(50) DEFAULT NULL,
  `var_name` varchar(255) NOT NULL,
  `text` mediumtext NOT NULL,
  `text_default` mediumtext NOT NULL,
  `added` int(10) unsigned NOT NULL,
  PRIMARY KEY (`phrase_id`),
  KEY `language_id` (`language_id`),
  KEY `module_id` (`module_id`,`var_name`),
  KEY `setting_list` (`language_id`,`var_name`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_announcement_announcements` (
  `announcement_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime DEFAULT NULL,
  `networks` text COLLATE utf8_unicode_ci,
  `member_levels` text COLLATE utf8_unicode_ci,
  `profile_types` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`announcement_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

 */
