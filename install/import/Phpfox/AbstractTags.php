<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractTags.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractTags extends Install_Import_Phpfox_Abstract
{

  protected $_fromResourceType;
  protected $_toResourceType;
  protected $_toTableTruncate = false;

  /* Moved to CleanupPre
    static protected $_toTableTruncated = false;
   */

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_fromResourceType', '_toResourceType' //, '_toTableTruncated', // That last one might not work
    ));
  }

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'tag';
    $this->_toTable = 'engine4_core_tagmaps';
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET TAG ID
    $tag_id = $this->getToDb()->select()
      ->from('engine4_core_tags', 'tag_id')
      ->where('text = ?', $data['tag_text'])
      ->query()
      ->fetchColumn();

    //CHECKING TAG EXIST OR NOT
    if( !$tag_id ) {
      $this->getToDb()->insert('engine4_core_tags', array(
        'text' => $data['tag_text']
      ));
      $tag_id = $this->getToDb()->lastInsertId();
    }

    //MAKING TAG DATA ARRAY
    $newData = array(
      'resource_type' => $data['category_id'],
      'resource_id' => $data['item_id'],
      'tagger_type' => 'user',
      'tagger_id' => $data['user_id'],
      'tag_type' => 'core_tag',
      'tag_id' => $tag_id,
      'creation_date' => $this->_translateTime($data['added'])
    );

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `item_id` int(10) unsigned NOT NULL,
  `category_id` varchar(75) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `tag_text` varchar(255) NOT NULL,
  `tag_url` varchar(255) NOT NULL,
  `added` int(10) unsigned NOT NULL,
  PRIMARY KEY (`tag_id`),
  KEY `user_id` (`user_id`,`tag_text`),
  KEY `item_id` (`item_id`,`category_id`),
  KEY `category_id` (`category_id`),
  KEY `tag_url` (`tag_url`),
  KEY `user_search` (`category_id`,`user_id`,`tag_text`),
  KEY `user_search_general` (`category_id`,`user_id`),
  KEY `item_id_2` (`item_id`,`category_id`,`user_id`),
  KEY `item_id_3` (`item_id`,`category_id`,`tag_url`),
  KEY `category_id_2` (`category_id`,`tag_text`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_tagmaps` (
  `tagmap_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `tagger_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tagger_id` int(11) unsigned NOT NULL,
  `tag_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `tag_id` int(11) unsigned NOT NULL,
  `creation_date` datetime DEFAULT NULL,
  `extra` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`tagmap_id`),
  KEY `resource_type` (`resource_type`,`resource_id`),
  KEY `tagger_type` (`tagger_type`,`tagger_id`),
  KEY `tag_type` (`tag_type`,`tag_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_tags` (
  `tag_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `text` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`tag_id`),
  UNIQUE KEY `text` (`text`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
