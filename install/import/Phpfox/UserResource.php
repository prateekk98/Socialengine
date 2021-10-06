<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserResource.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserResource extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('is_ignore=?' => 0, 'relation_data_id IS NULL' => NULL);
  protected $_priority = 1002;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'friend_request';
    $this->_toTable = 'engine4_user_membership';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //WHEN WE MAKE A FRIEND OR REQUEST FOR A FRIEND THEN TWO ENTRY MADE IN engine4_user_membership TABLE.ONE IS USER RESOURCE ENTRY AND SECOND ONE IS USER REQUEST ENTRY.
    //CREATING AN ARRAY FOR USER RESOURCE ENTRY.(PENDING REQUEST)
    $newData = array
      (
      'resource_id' => $data['user_id'],
      'user_id' => $data['friend_user_id'],
      'active' => 0,
      'resource_approved' => 0,
      'user_approved' => 1,
      'message' => $data['message']
    );


    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_friend_request` (
  `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `is_seen` tinyint(1) NOT NULL DEFAULT '0',
  `friend_user_id` int(10) unsigned NOT NULL,
  `is_ignore` tinyint(1) NOT NULL DEFAULT '0',
  `list_id` int(10) unsigned NOT NULL DEFAULT '0',
  `message` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `relation_data_id` int(10) unsigned DEFAULT NULL,
  PRIMARY KEY (`request_id`),
  KEY `user_id` (`user_id`,`friend_user_id`),
  KEY `ignored` (`user_id`,`is_ignore`),
  KEY `friend_user_id` (`friend_user_id`),
  KEY `relation_data_id` (`relation_data_id`),
  KEY `user_id_2` (`user_id`,`is_seen`,`is_ignore`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * 
CREATE TABLE IF NOT EXISTS `engine4_user_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `resource_approved` tinyint(1) NOT NULL DEFAULT '0',
  `user_approved` tinyint(1) NOT NULL DEFAULT '0',
  `message` text COLLATE utf8_unicode_ci,
  `description` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`resource_id`,`user_id`),
  KEY `REVERSE` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
