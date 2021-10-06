<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractLikes.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractLikes extends Install_Import_Phpfox_Abstract
{

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
    $this->_toTable = 'engine4_core_likes';
  }

  protected function _translateRow(array $data, $key = null)
  {
    $toType = $this->getToResourceType();
    $resourceId = $data['item_id'];
    //CHECK FOR GROUP [WE ARE SKIPPING THE COMMENTS FOR GROUP FOR BLOG, VIDEO]
    if( $toType == 'pages' ) {
      //FIND PAGES TYPE ID
      return false;
    } else if( $toType == 'blog' ) {
      //FIND PAGES TYPE ID
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'blog', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'blog.item_id', 'type_id')
        ->where($this->getfromPrefix() . 'blog.blog_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'blog.module_id = ?', 'pages')
        ->query()
        ->fetchColumn();
      if( $type_id ) {
        return false;
      }
    } else if( $toType == 'video' ) {
      //FIND PAGES TYPE ID
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'video', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'video.item_id', 'type_id')
        ->where($this->getfromPrefix() . 'video.video_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'video.module_id = ?', 'pages')
        ->query()
        ->fetchColumn();
      if( $type_id ) {
        return false;
      }
    } else if( $toType == 'music_playlist' ) {
      //FIND PAGES TYPE ID
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_album', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'music_album.item_id', 'type_id')
        ->where($this->getfromPrefix() . 'music_album.album_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'music_album.module_id = ?', 'pages')
        ->query()
        ->fetchColumn();
      if( $type_id ) {
        return false;
      }
    } else if( $toType == 'photo' ) {
      //GET BODY
      $photo_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo', 'photo_id')
        ->where('photo_id= ?', $data['item_id'])
        ->where('module_id= ?', 'pages')
        ->query()
        ->fetchColumn();

      if( $photo_id ) {
        $toType = 'group_photo';
      }
    }
    //PREPARING AN ARRAY TO INSERT CORE LIKE
    $newData = array(
      'resource_type' => $toType,
      'resource_id' => $resourceId,
      'poster_type' => 'user',
      'poster_id' => $data['user_id'],
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
 * CREATE TABLE IF NOT EXISTS `engine4_core_likes` (
  `like_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `resource_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `poster_type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `poster_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  PRIMARY KEY (`like_id`),
  KEY `resource_type` (`resource_type`,`resource_id`),
  KEY `poster_type` (`poster_type`,`poster_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
