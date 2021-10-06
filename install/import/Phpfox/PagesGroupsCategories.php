<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    PagesGroupsCategories.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_PagesGroupsCategories extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'pages_category';
    $this->_toTable = 'engine4_group_categories';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {
    //MAKING CATEGORY ARRAY FOR INSERTION
    $newData = array();
    $newData['category_id'] = $data['category_id'];
    $newData['title'] = $this->getPharseLabel($data['name']);
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_marketplace_category` (
  `category_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `name_url` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL DEFAULT '0',
  `used` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`,`is_active`),
  KEY `is_active` (`is_active`,`name_url`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_classified_categories` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `category_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
