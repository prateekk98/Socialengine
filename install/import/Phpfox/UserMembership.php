<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserMembership.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserMembership extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('is_page=?' => 0);
  protected $_warningMessage = array();
  protected $_priority = 1001;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'friend';
    $this->_toTable = 'engine4_user_membership';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _runPost()
  {

    $toDeleteRecords = $this->getToDb()
        ->query("select resource_id, user_id,count(*)-1 as count from engine4_user_membership group by resource_id, user_id having count>0")->fetchAll();
    $query = "";
    if( is_array($toDeleteRecords) ) {
      foreach( $toDeleteRecords as $rc ) {
        $query = "delete from engine4_user_membership where resource_id=" . $rc['resource_id'] . " and user_id=" . $rc['user_id'] . " limit " . $rc['count'] . " ;";
        $this->getToDb()->query($query);
      }
    }
    $this->getToDb()
      ->query(
        "ALTER TABLE engine4_user_membership ADD PRIMARY KEY (resource_id, user_id) USING BTREE;"
    );
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING THE FRIEND SHIP ARRAY FOR INSERTION
    $newData = array
      (
      'resource_id' => $data['friend_user_id'],
      'user_id' => $data['user_id'],
      'active' => 1,
      'resource_approved' => 1,
      'user_approved' => 1,
    );
    //UPDATE THE UPDATE COUNT OF FRIEND FOR THIS USER.
    $this->getToDb()->update
      ('engine4_users', array
      (
      'member_count' => new Zend_Db_Expr('member_count + 1'),
      ), array
      (
      'user_id = ?' => $data['user_id'],
      )
    );
    return $newData;
  }
}

/*
CREATE TABLE IF NOT EXISTS `phpfox_friend` (
  `friend_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_page` tinyint(1) NOT NULL DEFAULT '0',
  `list_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `friend_user_id` int(10) unsigned NOT NULL,
  `is_top_friend` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` tinyint(3) NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`friend_id`),
  UNIQUE KEY `user_check` (`user_id`,`friend_user_id`),
  KEY `user_id` (`user_id`),
  KEY `top_friend` (`user_id`,`is_top_friend`),
  KEY `friend_id` (`friend_id`,`user_id`),
  KEY `list_id` (`list_id`,`user_id`),
  KEY `friend_user_id` (`friend_user_id`),
  KEY `is_page` (`is_page`,`user_id`),
  KEY `is_page_2` (`is_page`,`user_id`,`friend_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 *
 */

/*
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
 *
 */
