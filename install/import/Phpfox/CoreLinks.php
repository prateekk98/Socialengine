<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    CoreLinks.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_CoreLinks extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('is_custom=?' => 0);
  protected $_priority = 97;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'link';
    $this->_toTable = 'engine4_core_links';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET PROFILE PAGE ID
    $profile_page_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'user', 'profile_page_id')
      ->where('profile_page_id != ?', 0)
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetchColumn();

    $subject_id = $data['user_id'];

    //GET SUBJECT ID 
    if( $profile_page_id ) {
      $subject_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'pages', 'user_id')
        ->where('page_id = ?', $profile_page_id)
        ->where('type_id = ?', 3)
        ->query()
        ->fetchColumn();
    }

    //MAKING CATEGORY ARRAY FOR INSERTION
    $newData = array();
    $newData['link_id'] = $data['link_id'];
    $newData['uri'] = $data['link'];
    $newData['title'] = $data['title'];
    $newData['description'] = $data['description'];
    $newData['parent_type'] = $data['module_id'] ? ($data['module_id'] == 'pages' ? 'group' : $data['module_id']) : 'user';
    $newData['parent_id'] = $data['item_id'] ? $data['item_id'] : $data['user_id'];
    $newData['owner_type'] = 'user';
    $newData['owner_id'] = $subject_id;
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['search'] = 1;

    return $newData;
  }
}

/*
CREATE TABLE IF NOT EXISTS `phpfox_link` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_custom` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status_info` mediumtext,
  `privacy` tinyint(1) NOT NULL,
  `privacy_comment` tinyint(1) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `has_embed` tinyint(1) NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`),
  KEY `parent_user_id` (`parent_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
CREATE TABLE IF NOT EXISTS `engine4_core_links` (
  `link_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `uri` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `parent_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `parent_id` int(11) unsigned NOT NULL,
  `owner_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `view_count` mediumint(6) unsigned NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`link_id`),
  KEY `owner` (`owner_type`,`owner_id`),
  KEY `parent` (`parent_type`,`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
