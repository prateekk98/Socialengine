<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserFbConnect.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserFbConnect extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_settingEnable = null;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'fbconnect';
    $this->_toTable = 'engine4_user_facebook';

    //GET FACEBOOK VALUE
    $value = $this->getToDb()->select()
      ->from('engine4_core_settings', 'value')
      ->where('name = ?', 'core.facebook.enable')
      ->query()
      ->fetchColumn();
    $this->_settingEnable = $value;
  }

  protected function _translateRow(array $data, $key = null)
  {

    //RETURN FALSE IF FACEBOOK LOGIN DISABLED
    if( empty($this->_settingEnable) || $this->_settingEnable == 'none' ) {
      return false;
    }
    //MAKING ARRAY FOR INSERTION OF FACEBOOK USER ID
    $newData = array();
    $newData['user_id'] = $data['user_id'];
    $newData['facebook_uid'] = $data['fb_user_id'];

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_fbconnect` (
  `user_id` int(10) unsigned NOT NULL,
  `fb_user_id` bigint(20) NOT NULL,
  `share_feed` tinyint(1) NOT NULL DEFAULT '0',
  `send_email` tinyint(1) NOT NULL DEFAULT '0',
  `is_proxy_email` tinyint(1) NOT NULL DEFAULT '0',
  `is_unlinked` tinyint(1) NOT NULL DEFAULT '0',
  UNIQUE KEY `user_id` (`user_id`),
  KEY `fb_user_id` (`fb_user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_user_facebook` (
  `user_id` int(11) unsigned NOT NULL,
  `facebook_uid` bigint(20) unsigned NOT NULL,
  `access_token` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `code` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `expires` bigint(20) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `facebook_uid` (`facebook_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
