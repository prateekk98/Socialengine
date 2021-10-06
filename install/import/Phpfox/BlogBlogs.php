<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    BlogBlogs.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_BlogBlogs extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();
  protected $_fromWhere = array('module_id=?' => 'blog', 'item_id=?' => 0);

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'blog';
    $this->_toTable = 'engine4_blog_blogs';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {


    $newData = array();
    //GETTING CATEGORY ID
    $category_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'blog_category_data', 'category_id')
      ->where('blog_id = ?', $data['blog_id'])
      ->query()
      ->fetchColumn();

    //GETTING TEXT
    $text = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'blog_text', 'text')
      ->where('blog_id = ?', $data['blog_id'])
      ->query()
      ->fetchColumn();

    if( is_null($text) || $text === false )
      $text = '';

    $userInfo = array(
      'user_id' => $data['user_id'],
      'time_stamp' => $data['time_stamp'],
      'text' => $text,
      'item_id' => $data['blog_id'],
      'album_title' => 'Blog Photos',
      'album_type' => 'blog',
      'categoryId' => 'blog'
    );
    //FIND THE BLOG BODY
    $body = $this->getBody($userInfo);
    if( is_null($body) ) {
      $body = '';
    }
    //MAKING BLOG ARRAY FOR INSERTION
    $newData['blog_id'] = $data['blog_id'];
    $newData['title'] = $data['title'];
    $newData['body'] = $body;
    $newData['owner_type'] = 'user';
    $newData['owner_id'] = $data['user_id'];
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);

    if( $data['time_update'] ) {
      $newData['modified_date'] = $this->_translateTime($data['time_update']);
    } else {
      $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    }
    $newData['view_count'] = $data['total_view'];
    $newData['comment_count'] = $data['total_comment'];
    $newData['search'] = $data['is_approved'];
    if( $data['post_status'] == 1 ) {
      $newData['draft'] = 0;
    } else {
      $newData['draft'] = 1;
    }
    $newData['category_id'] = $category_id;

    //set privacy
    $blogPrivacy = $this->_translateBlogPrivacy($data['privacy']);
    $newData['view_privacy'] = $blogPrivacy[0];

    //PRIVACY
    try {
      $this->_insertPrivacy('blog', $data['blog_id'], 'view', $this->_translateBlogPrivacy($data['privacy']));
      $this->_insertPrivacy('blog', $data['blog_id'], 'comment', $this->_translateBlogPrivacy($data['privacy_comment']));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $data['blog_id'] . ' : ' . $e->getMessage());
    }

    //SEARCH
    if( @$newData['search'] ) {
      $this->_insertSearch('blog', @$newData['blog_id'], @$newData['title'], @$newData['body']);
    }

    return $newData;
  }
}

/*
CREATE TABLE IF NOT EXISTS `phpfox_blog` (
  `blog_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `time_update` int(10) unsigned NOT NULL DEFAULT '0',
  `is_approved` tinyint(1) NOT NULL,
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `post_status` tinyint(1) NOT NULL,
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_attachment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `module_id` varchar(75) NOT NULL DEFAULT 'blog',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`),
  KEY `public_view` (`is_approved`,`privacy`,`post_status`),
  KEY `user_id_2` (`user_id`,`is_approved`,`privacy`,`post_status`),
  KEY `time_stamp` (`time_stamp`,`is_approved`,`privacy`,`post_status`),
  KEY `user_id` (`user_id`,`time_stamp`,`is_approved`,`privacy`,`post_status`),
  KEY `title` (`title`,`is_approved`,`privacy`,`post_status`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
 */

/*
CREATE TABLE IF NOT EXISTS `phpfox_blog_category_data` (
  `blog_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `blog_id` (`blog_id`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
CREATE TABLE IF NOT EXISTS `phpfox_blog_text` (
  `blog_id` int(10) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext,
  PRIMARY KEY (`blog_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
CREATE TABLE IF NOT EXISTS `engine4_blog_blogs` (
  `blog_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `body` longtext COLLATE utf8_unicode_ci NOT NULL,
  `owner_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `draft` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`blog_id`),
  KEY `owner_type` (`owner_type`,`owner_id`),
  KEY `search` (`search`,`creation_date`),
  KEY `owner_id` (`owner_id`,`draft`),
  KEY `draft` (`draft`,`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
