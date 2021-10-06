<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Ad.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_Ad extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'ad';
    $this->_toTable = 'engine4_core_adcampaigns';
    $this->_truncateTable($this->getToDb(), 'engine4_core_ads');
    $this->_truncateTable($this->getToDb(), 'engine4_core_adphotos');
    $this->_warningMessage = array("key" => 'escape', 'value' => '');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //SE Ad accept min in multiple of 10 ie (00,10,20,30,40,50) .so we convert min accourding to that.
    $stDateMin = date('i', $data['start_date']);
    if( ($stDateMin % 10) < 5 )
      $min = 0;
    else
      $min = 10;
    $stDateMin = (int) ($stDateMin / 10);
    if( $stDateMin > 0 )
      $stDateMin *= 10;
    $stDateMin += $min;
    $data['start_date'] = mktime(date('H', $data['start_date']), $stDateMin, 0, date('m', $data['start_date']), date('d', $data['start_date']), date('Y', $data['start_date']));


    $endDateMin = date('i', $data['end_date']);
    if( ($endDateMin % 10) < 5 )
      $min = 0;
    else
      $min = 10;
    $endDateMin = (int) ($endDateMin / 10);
    if( $endDateMin > 0 )
      $endDateMin *= 10;
    $endDateMin += $min;
    $data['end_date'] = mktime(date('H', $data['end_date']), $endDateMin, 0, date('m', $data['end_date']), date('d', $data['end_date']), date('Y', $data['end_date']));

    //MAKING CAMPAIGN ARRAY TO BE INSERTION
    $newData = array();
    $newData['adcampaign_id'] = $data['ad_id'];
    $newData['end_settings'] = $data['end_date'] ? 1 : 0;
    $newData['name'] = $data['name'];
    $newData['start_time'] = $this->_translateTime($data['start_date']);
    $newData['end_time'] = $data['end_date'] ? $this->_translateTime($data['end_date']) : '0000-00-00 00:00:00';
    $newData['limit_view'] = $data['total_view'];
    $newData['limit_click'] = $data['total_click'];
    $newData['views'] = $data['count_view'];
    $newData['clicks'] = $data['count_click'];
    $newData['status'] = $data['is_active'];
    $levels = array();
    if( is_null($data['user_group']) ) {
      //FIND LEVEL WHO WILL BE AUTHORIZED TO SEE ADS
      $allLevels = $this->getToDb()->select()
        ->from('engine4_authorization_levels', 'level_id')
        ->query()
        ->fetchAll();
      foreach( $allLevels as $level )
        $levels[] = $level['level_id'];
    } else {
      //FIND LEVEL WHO WILL BE AUTHORIZED TO SEE ADS
      $allowedGrp = unserialize($data['user_group']);
      if( count($allowedGrp) > 0 ) {
        $allowedGrpNames = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'user_group', 'title')
          ->where('user_group_id in ( ? ) ', $allowedGrp)
          ->query()
          ->fetchAll();
        foreach( $allowedGrpNames as $grp ) {
          $grpName = $grp['title'];
          $levels[] = $this->getLevelIdByTitleName($grpName);
        }
      }
    }
    $newData['level'] = json_encode($levels);

    //INSERT AD CAMPAIGNS
    $this->getToDb()->insert('engine4_core_adcampaigns', $newData);
    $adcampaign_id = $this->getToDb()->lastInsertId();

    //AD ARRAY
    $newAdData['ad_id'] = $data['ad_id'];
    $newAdData['name'] = $data['name'];
    $newAdData['ad_campaign'] = $data['ad_id'];
    $newAdData['views'] = $data['count_view'];
    $newAdData['clicks'] = $data['count_click'];
    $newAdData['media_type'] = $data['type_id'] == 2 ? 0 : 1;
    $newAdData['html_code'] = $data['html_code'] ? $data['html_code'] : '';

    //INSERT INTO ADS
    $this->getToDb()->insert('engine4_core_ads', $newAdData);

    $file_id = null;
    //CHECKING IMAGE PATH AND INSERT THE PHOTO
    if( !empty($data['image_path']) ) {
      $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/ad' . DIRECTORY_SEPARATOR . $data['image_path']);
      $file = $des[0];
      if( isset($des[1]) )
        $file = $des[0] . $des[1];
      if( $file ) {
        try {
          if( $this->getParam('resizePhotos', true) ) {
            $file_id = $this->_translatePhoto($file, array(
              'parent_type' => 'user',
              'parent_id' => $data['user_id'],
              'user_id' => @$data['user_id'],
            ));
          } else {
            $file_id = $this->_translateFile($file, array(
              'parent_type' => 'user',
              'parent_id' => $data['user_id'],
              'user_id' => @$data['user_id'],
              ), true);
          }
        } catch( Exception $e ) {
          $file_id = null;
          $this->_logFile($e->getMessage());
        }
      }
    }

    //INSERT DATA INTO PHOTO TABLE FOR AD
    $newPhotoData = array();
    $newPhotoData['creation_date'] = date('Y-m-d H:i:s');
    $newPhotoData['modified_date'] = date('Y-m-d H:i:s');
    if( $file_id ) {
      $newPhotoData['file_id'] = $file_id;

      //GETTING STORAGE PATH
      $storagePath = $this->getToDb()->select()
        ->from('engine4_storage_files', 'storage_path')
        ->where('file_id = ?', $file_id)
        ->query()
        ->fetchColumn();

      //INSERT INTO AD PHOTO 
      $this->getToDb()->insert('engine4_core_adphotos', $newPhotoData);
      $photoad_id = $this->getToDb()->lastInsertId();
      //UPDATE INTO CORE AD TABLE
      $this->getToDb()->update('engine4_core_ads', array(
        'photo_id' => $photoad_id,
        'html_code' => '<a href="" target="_blank"><img src="' . $storagePath . '" /></a>'
        ), array(
        'ad_id = ?' => $newData['adcampaign_id'],
      ));
    }
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_ad` (
  `ad_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `is_custom` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type_id` tinyint(1) NOT NULL,
  `name` varchar(150) NOT NULL,
  `url_link` mediumtext,
  `start_date` int(11) unsigned NOT NULL DEFAULT '0',
  `end_date` int(11) unsigned NOT NULL DEFAULT '0',
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `total_click` int(10) unsigned NOT NULL DEFAULT '0',
  `is_cpm` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `module_access` varchar(75) DEFAULT NULL,
  `location` varchar(50) NOT NULL,
  `country_iso` char(2) DEFAULT NULL,
  `gender` tinyint(1) NOT NULL DEFAULT '0',
  `age_from` tinyint(2) NOT NULL DEFAULT '0',
  `age_to` tinyint(2) NOT NULL DEFAULT '0',
  `user_group` varchar(255) DEFAULT NULL,
  `html_code` mediumtext,
  `count_view` int(10) unsigned NOT NULL DEFAULT '0',
  `count_click` int(10) unsigned NOT NULL DEFAULT '0',
  `image_path` varchar(75) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `gmt_offset` varchar(15) DEFAULT NULL,
  `disallow_controller` mediumtext,
  `postal_code` text,
  `city_location` text,
  PRIMARY KEY (`ad_id`),
  KEY `is_active` (`is_active`,`location`),
  KEY `is_custom` (`is_custom`,`type_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_adcampaigns` (
  `adcampaign_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `end_settings` tinyint(4) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime NOT NULL,
  `limit_view` int(11) unsigned NOT NULL DEFAULT '0',
  `limit_click` int(11) unsigned NOT NULL DEFAULT '0',
  `limit_ctr` varchar(11) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `network` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `level` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `views` int(11) unsigned NOT NULL DEFAULT '0',
  `clicks` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`adcampaign_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_adphotos` (
  `adphoto_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ad_id` int(11) unsigned NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`adphoto_id`),
  KEY `ad_id` (`ad_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_storage_files` (
  `file_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `parent_file_id` int(10) unsigned DEFAULT NULL,
  `type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `parent_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `service_id` int(10) unsigned NOT NULL DEFAULT '1',
  `storage_path` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `extension` varchar(8) COLLATE utf8_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mime_major` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `mime_minor` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `size` bigint(20) unsigned NOT NULL,
  `hash` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  PRIMARY KEY (`file_id`),
  UNIQUE KEY `parent_file_id` (`parent_file_id`,`type`),
  KEY `PARENT` (`parent_type`,`parent_id`),
  KEY `user_id` (`user_id`),
  KEY `service_id` (`service_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_ads` (
  `ad_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(16) COLLATE utf8_unicode_ci NOT NULL,
  `ad_campaign` int(11) unsigned NOT NULL,
  `views` int(11) unsigned NOT NULL DEFAULT '0',
  `clicks` int(11) unsigned NOT NULL DEFAULT '0',
  `media_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `html_code` text COLLATE utf8_unicode_ci NOT NULL,
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ad_id`),
  KEY `ad_campaign` (`ad_campaign`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 
 */
