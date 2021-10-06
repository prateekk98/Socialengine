<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    ForumForums.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_ForumForums extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_priority = 94;
  protected $_warningMessage = array();

  protected function _initPre()
  {
    if( $this->_tableExists($this->getToDb(), 'engine4_forum_categories') ) {
      $this->getToDb()->query('TRUNCATE TABLE' . $this->getToDb()->quoteIdentifier('engine4_forum_categories'));
    }
    $this->_fromTable = $this->getFromPrefix() . 'forum';
    $this->_toTable = 'engine4_forum_forums';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING NEW DATA ARRAY
    $newData = array();
    $newData['title'] = $this->getPharseLabel($data['name']);
    $newData['description'] = $data['description'] ? $data['description'] : $data['name'];
    $newData['creation_date'] = date('Y-m-d H:i:s');
    $newData['modified_date'] = date('Y-m-d H:i:s');
    $newData['order'] = $data['ordering'];

    //CHECK CATEGORY
    if( $data['is_category'] ) {
      $newData['category_id'] = $data['forum_id'];
      $this->getToDb()->insert('engine4_forum_categories', $newData);
    } else
      $newData['category_id'] = $data['parent_id'];

    $newData['forum_id'] = $data['forum_id'];


    //INSERT INTO FORUMS
    $this->getToDb()->insert('engine4_forum_forums', $newData);
    $newListData = array();
    $newListData['owner_id'] = $data['forum_id'];

    //INSERT INTO FORUM LISTS
    $this->getToDb()->insert('engine4_forum_lists', $newListData);
    $list_id = $this->getToDb()->lastInsertId();


    //GET MODRATOR AND INSERT INTO MODRATOR
    $moderators = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'forum_moderator', '*')
      ->where('forum_id = ?', $data['forum_id'])
      ->query()
      ->fetchAll();
    $count = count($moderators);
    $newModratorData = array();
    foreach( $moderators as $value ) {
      $newModratorData['list_id'] = $list_id;
      $newModratorData['child_id'] = $value['user_id'];
      $this->getToDb()->insert('engine4_forum_listitems', $newModratorData);
    }

    //UPDATE FORUM LIST COUNT
    $this->getToDb()->update('engine4_forum_lists', array(
      'child_count' => $count,
      ), array(
      'owner_id = ?' => $data['forum_id'],
    ));

    //PRIVACY
    $this->getToDb()
      ->query('INSERT IGNORE INTO `engine4_authorization_allow` (
SELECT "forum" as `resource_type`, forum_id as `resource_id`, "view" as `action`,"everyone" as `role`, 0 as `role_id`,1 as `value`,NULL as `params` FROM `engine4_forum_forums`);');
    $this->getToDb()
      ->query('INSERT IGNORE INTO `engine4_authorization_allow` (SELECT "forum" as `resource_type`, forum_id as `resource_id`, "topic.create" as `action`,"registered" as `role`,0 as `role_id`,1 as `value`, NULL as `params` FROM `engine4_forum_forums`);');
    $this->getToDb()
      ->query('INSERT IGNORE INTO `engine4_authorization_allow` (SELECT "forum" as `resource_type`, forum_id as `resource_id`, "post.create" as `action`,"registered" as `role`,0 as `role_id`,1 as `value`, NULL as `params` FROM `engine4_forum_forums`);');
    $this->getToDb()
      ->query('INSERT IGNORE INTO `engine4_authorization_allow` (SELECT "forum" as `resource_type`, owner_id as `resource_id`, "topic.edit" as `action`,"forum_list" as `role`,list_id as `role_id`,1 as `value`, NULL as `params` FROM `engine4_forum_lists`);');
    $this->getToDb()
      ->query('INSERT IGNORE INTO `engine4_authorization_allow` (SELECT "forum" as `resource_type`, owner_id as `resource_id`, "topic.delete" as `action`,"forum_list" as `role`,list_id as `role_id`,1 as `value`, NULL as `params` FROM `engine4_forum_lists`);');
    $this->getToDb()
      ->query('INSERT IGNORE INTO `engine4_authorization_allow` (SELECT "forum" as `resource_type`, forum_id as `resource_id`, "comment" as `action`,"registered" as `role`,0 as `role_id`,1 as `value`, NULL as `params` FROM `engine4_forum_forums`);');


    //UPDATE CATEGORY
    if( !$newData['category_id'] ) {

      $category_id = $this->getToDb()->select()
        ->from('engine4_forum_forums', 'category_id')
        ->where('category_id <> ?', 0)
        ->order('category_id DESC')
        ->query()
        ->fetchColumn();
      $this->getToDb()->update('engine4_forum_forums', array(
        'category_id' => $category_id,
        ), array(
        'forum_id = ?' => $data['forum_id'],
      ));
    }
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_forum` (
  `forum_id` smallint(4) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `is_category` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `name_url` varchar(255) NOT NULL,
  `description` mediumtext,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `thread_id` int(10) unsigned NOT NULL DEFAULT '0',
  `post_id` int(10) unsigned NOT NULL DEFAULT '0',
  `last_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `total_post` int(10) unsigned NOT NULL DEFAULT '0',
  `total_thread` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  KEY `view_id` (`view_id`),
  KEY `post_id` (`post_id`),
  KEY `thread_id` (`thread_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_forums` (
  `forum_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `category_id` int(11) unsigned NOT NULL,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `order` smallint(6) NOT NULL DEFAULT '999',
  `file_id` int(11) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `topic_count` int(11) unsigned NOT NULL DEFAULT '0',
  `post_count` int(11) unsigned NOT NULL DEFAULT '0',
  `lastpost_id` int(11) unsigned NOT NULL DEFAULT '0',
  `lastposter_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`forum_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_categories` (
  `category_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `order` smallint(6) NOT NULL DEFAULT '0',
  `forum_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `order` (`order`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_lists` (
  `list_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `owner_id` int(11) unsigned NOT NULL,
  `child_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`list_id`),
  KEY `owner_id` (`owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_forum_listitems` (
  `listitem_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `list_id` int(11) unsigned NOT NULL,
  `child_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`listitem_id`),
  KEY `list_id` (`list_id`),
  KEY `child_id` (`child_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * 
CREATE TABLE IF NOT EXISTS `engine4_authorization_allow` (
  `resource_type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `resource_id` int(11) unsigned NOT NULL,
  `action` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `role` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `role_id` int(11) unsigned NOT NULL DEFAULT '0',
  `value` tinyint(1) NOT NULL DEFAULT '0',
  `params` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`resource_type`,`resource_id`,`action`,`role`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
