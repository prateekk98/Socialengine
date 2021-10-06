<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserListItems.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserListItems extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'friend_list_data';
    $this->_toTable = 'engine4_user_listitems';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING FRIENDS ITEM LIST ARRAY FOR INSERTION
    $newData = array();
    $newData['list_id'] = $data['list_id'];
    $newData['child_id'] = $data['friend_user_id'];
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_friend_list_data` (
  `list_id` int(10) unsigned NOT NULL DEFAULT '0',
  `friend_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `list_id` (`list_id`,`friend_user_id`),
  KEY `list_id_2` (`list_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_user_listitems` (
  `listitem_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `list_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`listitem_id`),
  KEY `list_id` (`list_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;

 */
