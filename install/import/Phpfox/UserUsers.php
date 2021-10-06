<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserUsers.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserUsers extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('profile_page_id=?' => 0);
  protected $_priority = 6000;
  protected $_staticSalt = null;

  protected function _initPre()
  {

    //GET STATIC SALT
    $this->_staticSalt = (string) $this->getToDb()->select()
        ->from('engine4_core_settings', 'value')
        ->where('name = ?', 'core.secret')
        ->limit(1)
        ->query()
        ->fetchColumn(0)
    ;

    if( !$this->_staticSalt ) {
      $this->_staticSalt = 'staticSalt';
    }
    $this->_fromTable = $this->getFromPrefix() . 'user';
    $this->_toTable = 'engine4_users';
  }

  protected function _runPost()
  {

    //INSERT THE AUTHORIZATION PERMISSION FOR EACH LEVEL
    $this->_insertMemberLevel();
  }

  protected function _translateRow(array $data, $key = null)
  {

    $newData = array();
    $newData['user_id'] = $data['user_id'];
    $newData['email'] = $data['email'];
    $newData['username'] = trim($data['user_name']);
    $newData['displayname'] = trim((string) @$data['full_name']);
    $newData['search'] = $data['is_invisible'];
    $newData['status'] = trim((string) @$data['status']);
    $newData['search'] = !$data['is_invisible'];
    $newData['enabled'] = 1;
    $newData['verified'] = 1;
    $newData['approved'] = 1;
    $newData['creation_date'] = $this->_translateTime($data['joined']);
    $timezone = timezone_identifiers_list();
    $newData['timezone'] = $data['time_zone'] ? (isset($timezone[str_replace('z', '', $data['time_zone'])]) ? ($timezone[str_replace('z', '', $data['time_zone'])]) : 'America/Los_Angeles') : 'America/Los_Angeles';
    $newData['lastlogin_date'] = $this->_translateTime($data['last_login']);
    $newData['lastlogin_ip'] = $data['last_ip_address'];
    $newData['show_profileviewers'] = $data['view_id'];

    // search
    if( @$newData['search'] ) {
      $this->_insertSearch('user', @$newData['user_id'], @$newData['displayname'], @$newData['status']);
    }
    $newData['salt'] = (string) rand(1000000, 9999999);
    if( $this->getParam('checkmode') == 1 ) {
      $passwordRaw = $this->getParam('password', 123456);
      if( !$passwordRaw ) {
        $passwordRaw = '123456';
      }
      $newData['password'] = md5($this->_staticSalt . $passwordRaw . $newData['salt']);
    } else {
      $passwordRaw = $this->_generateRandomPassword();
      $newData['password'] = md5($this->_staticSalt . $passwordRaw . $newData['salt']);
      if( 'random' == $this->getParam('passwordRegeneration') ) {

        $banDataInfo = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'ban_data', '*')
          ->from($this->getfromPrefix() . 'user', $this->getfromPrefix() . 'user.user_id=' . $this->getfromPrefix() . 'ban_data.user_id', 'email')
          ->where('is_expired =?', 0)
          ->order('ban_data_id DESC')
          ->query()
          ->fetch();

        $emailSent = 1;
        if( !is_null($banDataInfo['email']) && !empty($banDataInfo['email']) ) {
          if( $banDataInfo['email'] == $newData['email'] ) {
            $emailSent = 0;
          }
        }

        if( $emailSent ) {
          // Email them a generated password
          //if ('random' == $this->getParam('passwordRegeneration')) {
          // Make password
          // Make email
          $fromAddress = $this->getParam('mailFromAddress');
          $subject = $this->getParam('mailSubject');
          $message = $this->getParam('mailTemplate');

          $search = array(
            '{name}',
            '{siteUrl}',
            '{email}',
            '{password}',
          );

          $replace = array(
            $newData['displayname'],
            'http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF']))),
            $newData['email'],
            $passwordRaw,
          );

          list($subject, $message) = str_replace($search, $replace, array($subject, $message));

          // Make mail
          $messageText = strip_tags($message);

          if( $messageText == $message ) {
            $message = nl2br($message);
          }

          $mail = new Zend_Mail();
          $mail
            ->setFrom($fromAddress)
            ->addTo($newData['email'])
            ->setSubject($subject)
            ->setBodyHtml($message)
            ->setBodyText($messageText)
          ;

          try {
            $this->getToDb()->insert('engine4_core_mail', array(
              'type' => 'zend',
              'body' => serialize($mail),
              'priority' => 200,
              'recipient_count' => 1,
              'recipient_total' => 1,
              'creation_time' => new Zend_Db_Expr('NOW()'),
            ));
          } catch( Exception $e ) {
            $this->_error($e);
          }
        }
      }
    }
    //CHECKING FOR PROFILE IMAGE OF USER AND IF EXIST THEN INSERT THAT PROFILE IMAGE.
    if( $data['user_image'] ) {
      try {
        $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/user' . DIRECTORY_SEPARATOR . $data['user_image']);
        $file = $des[0];
        if( isset($des[1]) )
          $file = $des[0] . $des[1];
        $file_id = $this->_translatePhoto($file, array(
          'parent_type' => 'user',
          'parent_id' => $data['user_id'],
          'user_id' => $data['user_id'],
        ));
      } catch( Exception $e ) {
        $file_id = null;
        $this->_logFile($e->getMessage());
      }
      if( $file_id ) {
        $newData['photo_id'] = $file_id;
      }
    }

    // FETCHING OTHER PROFILE DATA FOR THIS USER
    $this->_otherProfileData($newData, $data);
    //GET PRIVACY VALUE
    $comment_user_value = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'user_privacy', 'user_value')
      ->where('user_privacy = ?', 'feed.share_on_wall')
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetchColumn();

    $comment_user_value = $comment_user_value ? $comment_user_value : 1;

    $profile_user_value = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'user_privacy', 'user_value')
      ->where('user_privacy = ?', 'profile.view_profile')
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetchColumn();

    $profile_user_value = $profile_user_value ? $profile_user_value : 0;
    //PRIVACY
    try {
      //set privacy
      $userPrivacy = $this->_translateUserPrivacy($profile_user_value);
      $newData['view_privacy'] = $userPrivacy[0];

      $this->_insertPrivacy('user', $data['user_id'], 'view', $this->_translateUserPrivacy($profile_user_value));
      $this->_insertPrivacy('user', $data['user_id'], 'comment', $this->_translateUserPrivacy($comment_user_value));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $data['user_id'] . ' : ' . $e->getMessage());
    }
    return $newData;
  }

  protected function _generateRandomPassword($length = 8, $charlist = null)
  {
    if( !is_int($length) || $length < 1 || $length > 32 ) {
      $length = 8;
    }
    if( !$charlist ) {
      $charlist = 'abcdefghjkmnpqstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // No iolIO10
    }

    $password = '';
    do {
      $password .= $charlist[rand(0, strlen($charlist) - 1)];
    } while( strlen($password) < $length );

    return $password;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_user` (
  `user_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `profile_page_id` int(10) unsigned NOT NULL DEFAULT '0',
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `user_group_id` smallint(4) unsigned NOT NULL,
  `status_id` tinyint(2) NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `user_name` varchar(100) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `password` char(32) DEFAULT NULL,
  `password_salt` char(3) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `gender` tinyint(3) NOT NULL DEFAULT '0',
  `birthday` char(10) DEFAULT NULL,
  `birthday_search` bigint(20) NOT NULL DEFAULT '0',
  `country_iso` char(2) DEFAULT NULL,
  `language_id` varchar(12) DEFAULT NULL,
  `style_id` smallint(4) unsigned NOT NULL DEFAULT '0',
  `time_zone` char(4) DEFAULT NULL,
  `dst_check` tinyint(1) NOT NULL DEFAULT '0',
  `joined` int(10) unsigned NOT NULL,
  `last_login` int(10) unsigned NOT NULL DEFAULT '0',
  `last_activity` int(10) unsigned NOT NULL DEFAULT '0',
  `user_image` varchar(75) DEFAULT NULL,
  `hide_tip` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(255) DEFAULT NULL,
  `footer_bar` tinyint(1) NOT NULL DEFAULT '0',
  `invite_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `im_beep` tinyint(1) NOT NULL DEFAULT '0',
  `im_hide` tinyint(1) NOT NULL DEFAULT '0',
  `is_invisible` tinyint(1) NOT NULL DEFAULT '0',
  `total_spam` smallint(4) unsigned NOT NULL DEFAULT '0',
  `last_ip_address` varchar(15) DEFAULT NULL,
  `feed_sort` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  KEY `user_name` (`user_name`),
  KEY `email` (`email`),
  KEY `user_image` (`user_image`),
  KEY `user_group_id` (`user_group_id`),
  KEY `user_status` (`status_id`),
  KEY `total_spam` (`total_spam`),
  KEY `status_id` (`status_id`,`view_id`),
  KEY `public_feed` (`status_id`,`view_id`,`last_activity`),
  KEY `status_id_2` (`status_id`,`view_id`,`full_name`),
  KEY `page_id` (`profile_page_id`),
  KEY `user_id` (`user_id`,`status_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_users` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `username` varchar(128) COLLATE utf8_unicode_ci DEFAULT NULL,
  `displayname` varchar(128) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status` text COLLATE utf8_unicode_ci,
  `status_date` datetime DEFAULT NULL,
  `password` char(32) COLLATE utf8_unicode_ci NOT NULL,
  `salt` char(64) COLLATE utf8_unicode_ci NOT NULL,
  `locale` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'auto',
  `language` varchar(8) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'en_US',
  `timezone` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL DEFAULT 'America/Los_Angeles',
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `show_profileviewers` tinyint(1) NOT NULL DEFAULT '1',
  `level_id` int(11) unsigned NOT NULL,
  `invites_used` int(11) unsigned NOT NULL DEFAULT '0',
  `extra_invites` int(11) unsigned NOT NULL DEFAULT '0',
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `verified` tinyint(1) NOT NULL DEFAULT '0',
  `approved` tinyint(1) NOT NULL DEFAULT '1',
  `creation_date` datetime NOT NULL,
  `creation_ip` varbinary(16) NOT NULL,
  `modified_date` datetime NOT NULL,
  `lastlogin_date` datetime DEFAULT NULL,
  `lastlogin_ip` varbinary(16) DEFAULT NULL,
  `update_date` int(11) DEFAULT NULL,
  `member_count` smallint(5) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `user_cover` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `EMAIL` (`email`),
  UNIQUE KEY `USERNAME` (`username`),
  KEY `MEMBER_COUNT` (`member_count`),
  KEY `CREATION_DATE` (`creation_date`),
  KEY `search` (`search`),
  KEY `enabled` (`enabled`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
