<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AlbumCategories.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_AlbumCategories extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('parent_id=?' => 0);
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'photo_category';
    $this->_toTable = 'engine4_album_categories';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING CATEGORY ARRAY FOR INSERTION
    $newData = array();
    $newData['category_id'] = $data['category_id'];
    $newData['category_name'] = $this->getPharseLabel($data['name']);
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_category_data` (
  `photo_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `photo_id` (`photo_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */


/*
 * CREATE TABLE IF NOT EXISTS `engine4_album_categories` (
  `category_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `category_name` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`category_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
