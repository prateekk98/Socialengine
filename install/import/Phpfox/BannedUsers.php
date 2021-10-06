<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    BannedUsers.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_BannedUsers extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromJoinTable = '';
  protected $_fromJoinCondition = '';

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'ban_data';
    $this->_toTable = 'engine4_core_bannedemails';
    $this->_fromJoinTable = $this->getFromPrefix() . 'user';
    $this->_fromJoinCondition = $this->getFromPrefix() . 'user.user_id=' . $this->getfromPrefix() . 'ban_data.user_id';
  }

  protected function _translateRow(array $data, $key = null)
  {
    $newData = array();

    //CHECKING FOR USER EMAIL ID ,BAN START TIME AND BAN END TIME SHOULD NOT BE NULL.
    if( !is_null($data['email']) && !empty($data['email']) && !is_null($data['end_time_stamp']) && !is_null($data['start_time_stamp']) ) {
      //CHECKING FOR CURRENT TIME IN B/W OF START TIME AND END TIME.
      if( time() > $data['start_time_stamp'] && time() <= $data['end_time_stamp'] ) {
        //CHECKING FOR RECORD ALREADY INSERTED OR NOT
        $isBanned = $this->getToDb()
          ->select()
          ->from('engine4_core_bannedemails', 'bannedemail_id')
          ->where('email = ?', $data['email'])
          ->limit(1)
          ->query()
          ->fetchColumn(0);
        //IF NOT THEN PREPARE AN ARRAY FOR BANNED USER
        if( $isBanned === false ) {
          $newData['email'] = $data['email'];
          return $newData;
        }
      }
    }
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_ban_data` (
  `ban_data_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ban_id` int(11) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `start_time_stamp` int(10) unsigned NOT NULL,
  `end_time_stamp` int(10) unsigned NOT NULL,
  `return_user_group` tinyint(3) NOT NULL,
  `reason` mediumtext,
  `is_expired` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ban_data_id`),
  KEY `ban_id` (`ban_id`,`user_id`,`end_time_stamp`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
CREATE TABLE IF NOT EXISTS `engine4_core_bannedemails` (
  `bannedemail_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`bannedemail_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
