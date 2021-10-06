<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserLists.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserLists extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'friend_list';
    $this->_toTable = 'engine4_user_lists';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING Friend List ARRAY FOR INSERTION
    $newData = array();
    $newData['list_id'] = $data['list_id'];
    $newData['owner_id'] = $data['user_id'];
    $newData['title'] = $data['name'];
    $newData['child_count'] = $this->findFriendListCount($data['list_id']);
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_friend_list` (
  `list_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `is_profile` tinyint(3) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`list_id`),
  KEY `user_id` (`user_id`),
  KEY `list_id` (`list_id`,`user_id`),
  KEY `user_id_2` (`user_id`,`is_profile`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * 
CREATE TABLE IF NOT EXISTS `engine4_user_lists` (
  `list_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `owner_id` int(11) unsigned NOT NULL,
  `child_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`list_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
