<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserBlock.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserBlock extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'user_blocked';
    $this->_toTable = 'engine4_user_block';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING BLOCKED USER ARRAY FOR INSERTION
    $newData = array();
    $newData['user_id'] = $data['user_id'];
    $newData['blocked_user_id'] = $data['block_user_id'];
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_user_blocked` (
  `block_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `block_user_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`block_id`),
  KEY `user_id` (`user_id`,`block_user_id`),
  KEY `user_id_2` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_user_block` (
  `user_id` int(11) unsigned NOT NULL,
  `blocked_user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`blocked_user_id`),
  KEY `REVERSE` (`blocked_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
