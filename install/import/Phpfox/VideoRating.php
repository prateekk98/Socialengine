<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    VideoRating.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_VideoRating extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'video_rating';
    $this->_toTable = 'engine4_video_ratings';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING RATING ARRAY FOR INSERTION
    $newData = array();
    $newData['video_id'] = $data['item_id'];
    $newData['user_id'] = $data['user_id'];
    $newData['rating'] = (int) ($data['rating'] / 2);
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_video_rating` (
  `rate_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `rating` decimal(4,2) NOT NULL DEFAULT '0.00',
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`rate_id`),
  KEY `item_id` (`item_id`,`user_id`),
  KEY `item_id_2` (`item_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */


/*
 * CREATE TABLE IF NOT EXISTS `engine4_video_ratings` (
  `video_id` int(10) unsigned NOT NULL,
  `user_id` int(9) unsigned NOT NULL,
  `rating` tinyint(1) unsigned DEFAULT NULL,
  PRIMARY KEY (`video_id`,`user_id`),
  KEY `INDEX` (`video_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
