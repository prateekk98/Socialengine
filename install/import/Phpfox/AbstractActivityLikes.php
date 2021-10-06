<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractActivityLikes.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractActivityLikes extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromResourceType;
  protected $_toResourceType;
  protected $_priority = 90;

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_fromResourceType', '_toResourceType'
    ));
  }

  public function getFromResourceType()
  {
    if( null === $this->_fromResourceType ) {
      throw new Engine_Exception('No resource type');
    }
    return $this->_fromResourceType;
  }

  public function getToResourceType()
  {
    if( null === $this->_toResourceType ) {
      throw new Engine_Exception('No resource type');
    }
    return $this->_toResourceType;
  }

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'like';
    $this->_toTable = 'engine4_activity_likes';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET RESOURCE TYPE
    $toType = $this->getToResourceType();
    $itemId = $data['item_id'];
    //GET FEED ID
    $data['item_id'] = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'feed', 'feed_id')
      ->where('type_id = ?', $toType)
      ->where('item_id = ?', $data['item_id'])
      ->query()
      ->fetchColumn();

    //RETURN FALSE IF DATA ITEM ID NOT EXIST
    if( !$data['item_id'] )
      return false;

    //MAKING DATA ARRAY
    $newData = array(
      'resource_id' => $data['item_id'],
      'poster_type' => 'user',
      'poster_id' => $data['user_id']
    );



    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_like` (
  `like_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `type_id` varchar(75) NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `type_id` (`type_id`,`item_id`),
  KEY `type_id_2` (`type_id`,`item_id`,`user_id`),
  KEY `type_id_3` (`type_id`,`user_id`),
  KEY `item_id` (`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * 
CREATE TABLE IF NOT EXISTS `phpfox_feed` (
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `type_id` varchar(75) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `feed_reference` int(10) NOT NULL DEFAULT '0',
  `parent_feed_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_module_id` varchar(75) DEFAULT NULL,
  `time_update` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`phpfox_feedfeed_id`),
  KEY `privacy_2` (`privacy`,`time_stamp`,`feed_reference`),
  KEY `privacy_3` (`privacy`,`user_id`,`feed_reference`),
  KEY `privacy_4` (`privacy`,`parent_user_id`,`feed_reference`),
  KEY `type_id` (`type_id`,`item_id`,`feed_reference`),
  KEY `privacy` (`privacy`,`user_id`,`time_stamp`,`feed_reference`),
  KEY `time_stamp` (`time_stamp`,`feed_reference`),
  KEY `time_update` (`time_update`),
  KEY `privacy_5` (`privacy`,`parent_user_id`),
  KEY `user_id` (`user_id`,`feed_reference`,`time_stamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */ 

/*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_likes` (
  `like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(16) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `resource_id` (`resource_id`),
  KEY `poster_type` (`poster_type`,`poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
