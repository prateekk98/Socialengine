<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    CoreSettings.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_CoreSettings extends Install_Import_Phpfox_Abstract
{

  protected $_toTableTruncate = false;
  protected $toDb = null;

  protected function _run()
  {
    //FIND ALL THE SETTING
    $data = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'setting', '*')
      ->order('setting_id ASC')
      //->limit(1)
      ->query()
      ->fetchAll()
    ;

    if( Zend_Registry::isRegistered('Zend_Db') && ($db = Zend_Registry::get('Zend_Db')) instanceof Zend_Db_Adapter_Abstract ) {
      try {
        //FIND THE USER DETAILS
        $userDetails = $db->query('
          SELECT email, password, salt
          FROM engine4_users
          LEFT JOIN engine4_authorization_levels
          ON engine4_authorization_levels.level_id=engine4_users.level_id
          WHERE 
          type= \'admin\'         
          ORDER BY user_id ASC
          LIMIT 1
        ')->fetchAll();
      } catch( Exception $e ) {
        // Silence
      }
      //FIND THE SUPER ADMIN EMAIL ID
      $emailid_superadmin = $this->getParam('emailid_superadmin');
      $existEmail = $db->query("
          SELECT email, password, salt
          FROM engine4_users
          WHERE email = '$emailid_superadmin' 
          LIMIT 1
        ")->fetchAll();
    }
    $this->toDb = $this->getToDb();
    //UPDATE THE SUPER ADMIN PASSWORD,SALT AND EMAIL ID
    if( !$existEmail ) {
      $password = $userDetails[0]['password'];
      $salt = $userDetails[0]['salt'];
      $email = $userDetails[0]['email'];
      if( $this->getParam('superadminpassword') ) {
        $staticSalt = (string) $this->getToDb()->select()
            ->from('engine4_core_settings', 'value')
            ->where('name = ?', 'core.secret')
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        $salt = (string) rand(1000000, 9999999);
        $password = md5($staticSalt . $this->getParam('superadminpassword') . $salt);
      }

      $this->toDb->query("update engine4_users set level_id = 1,email='$emailid_superadmin', password='$password', salt= '$salt' where  email='$email'");
    } else {
      $password = $existEmail[0]['password'];
      $salt = $existEmail[0]['salt'];
      $email = $existEmail[0]['email'];
      if( $this->getParam('superadminpassword') ) {
        $staticSalt = (string) $this->getToDb()->select()
            ->from('engine4_core_settings', 'value')
            ->where('name = ?', 'core.secret')
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        $salt = (string) rand(1000000, 9999999);
        $password = md5($staticSalt . $this->getParam('superadminpassword') . $salt);
      }
      $this->toDb->query("update engine4_users set level_id = 1, password='$password', salt= '$salt' where  email='$email'");
    }

    if( empty($data) ) {
      $this->_warning('No settings found', 0);
      return;
    }

    // UPDATE THE CORE SETTINGS
    foreach( $data as $value ) {

      switch( $value['var_name'] ) {
        case 'facebook_app_id' :
          $this->_setSetting('core.facebook.appid', $value['value_actual'], '');
          break;
        case 'facebook_secret' :
          $this->_setSetting('core.facebook.secret', $value['value_actual'], '');
          break;
        case 'site_title' :
          $this->_setSetting('core.site.title', $value['value_actual'], '');
          $this->_setSetting('core.general.site.title', $value['value_actual'], '');
          break;
        case 'keywords' :
          $this->_setSetting('core.general.site.keywords', $value['value_actual'], '');
          break;
        case 'description' :
          $this->_setSetting('core.general.site.description', $value['value_actual'], '');
          break;
        case 'cdn_cname' :
          $this->_setSetting('core.general.staticBaseUrl', $value['value_actual'], '');
          break;
        case 'enable_facebook_connect' :
          if( $value['value_actual'] ) {
            $this->_setSetting('core.facebook.enable', 'login', '');
          } else {
            $this->_setSetting('core.facebook.enable', 'none', '');
          }
          break;
        case 'ffmpeg_path' :
          $this->_setSetting('video.ffmpeg.path', $value['value_actual'], '');

          break;
        case 'blog_meta_description' :

          $pages = array
            (
            "'blog_index_index'",
            "'blog_index_manage'",
            "'blog_index_list'"
          );

          $this->updateCorePagesDesc($pages, $value['value_actual']);
          break;
        case 'blog_meta_keywords' :
          $pages = array
            (
            "'blog_index_index'",
            "'blog_index_manage'",
            "'blog_index_list'"
          );
          $this->updateCorePagesKeyword($pages, $value['value_actual']);
          break;
        case 'video_meta_description' :
          $pages = array
            (
            "'video_index_browse'",
            "'video_index_manage'"
          );
          $this->updateCorePagesDesc($pages, $value['value_actual']);
          break;

        case 'video_meta_keywords' :
          $pages = array
            (
            "'video_index_browse'",
            "'video_index_manage'"
          );
          $this->updateCorePagesKeyword($pages, $value['value_actual']);
          break;
        case 'poll_meta_description' :
          $pages = array
            (
            "'poll_index_manage'",
            "'poll_index_browse'",
            "'poll_poll_view'"
          );
          $this->updateCorePagesDesc($pages, $value['value_actual']);
          break;

        case 'poll_meta_keywords' :
          $pages = array
            (
            "'poll_index_manage'",
            "'poll_index_browse'",
            "'poll_poll_view'"
          );
          $this->updateCorePagesKeyword($pages, $value['value_actual']);
          break;
        case 'photo_meta_description' :
          $pages = array
            (
            "'album_photo_view'",
            "'album_album_view'",
          );
          $this->updateCorePagesDesc($pages, $value['value_actual']);
          break;

        case 'photo_meta_keywords' :
          $pages = array
            (
            "'album_photo_view'",
            "'album_album_view'",
          );
          $this->updateCorePagesKeyword($pages, $value['value_actual']);
          break;
        case 'meta_description_profile' :
          $pages = array
            (
            "'user_profile_index'",
          );
          $this->updateCorePagesDesc($pages, $value['value_actual']);
          break;
        case 'recaptcha_public_key':
          $this->_setSetting('core.spam.recaptchapublic', $value['value_actual'], '');
          break;
        case 'recaptcha_private_key':
          $this->_setSetting('core.spam.recaptchaprivate', $value['value_actual'], '');
          break;
      }
    }
  }
  /*
   * update the description meta tag of page
   */

  public function updateCorePagesDesc($pageName, $desc)
  {

    $this->toDb->query
      (
      "update engine4_core_pages set description='$desc'
                            where  name in (" . implode($pageName, ',') . ")"
    );
  }
  /*
   * update the keyword meta tag of page
   */

  public function updateCorePagesKeyword($pageName, $key)
  {
    $this->toDb->query
      (
      "update engine4_core_pages set keywords='$key'
                            where  name in (" . implode($pageName, ',') . ")"
    );
  }
  /*
   * UPDATE THE CORE SETTING
   */

  protected function _setSetting($key, $value, $default = null)
  {
    if( null === $value ) {
      $value = $default;
    }
    $this->_insertOrUpdate($this->getToDb(), 'engine4_core_settings', array(
      'name' => $key,
      'value' => $value,
      ), array(
      'value' => $value,
    ));
  }

  protected function _translateRow(array $data, $key = null)
  {
    return false;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_setting` (
  `setting_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_id` varchar(75) DEFAULT NULL,
  `module_id` varchar(75) DEFAULT NULL,
  `product_id` varchar(25) NOT NULL DEFAULT 'phpfox',
  `is_hidden` tinyint(1) NOT NULL DEFAULT '0',
  `version_id` varchar(50) DEFAULT NULL,
  `type_id` varchar(255) NOT NULL,
  `var_name` varchar(100) NOT NULL,
  `phrase_var_name` varchar(250) NOT NULL,
  `value_actual` mediumtext,
  `value_default` mediumtext,
  `ordering` tinyint(3) NOT NULL DEFAULT '0',
  PRIMARY KEY (`setting_id`),
  KEY `var_name` (`var_name`),
  KEY `group_id` (`group_id`,`is_hidden`),
  KEY `module_id` (`module_id`,`is_hidden`),
  KEY `product_id` (`product_id`,`is_hidden`),
  KEY `is_hidden` (`is_hidden`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_settings` (
  `name` varchar(255) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `value` longtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
