<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    PollPolls.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_PollPolls extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('item_id=?' => 0);
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'poll';
    $this->_toTable = 'engine4_poll_polls';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //MAKING ARRAY FOR POLL INSERTION
    $newData = array();
    $newData['poll_id'] = $data['poll_id'];
    $newData['user_id'] = $data['user_id'];
    $newData['is_closed'] = 0;
    $newData['title'] = $data['question'];
    $newData['description'] = '';
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['view_count'] = $data['total_view'];
    $newData['comment_count'] = $data['total_comment'];
    $newData['search'] = 1;

    // FIND VOTE COUNT OF THIS POLL
    $pollVoteCount = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'poll_result', 'count(*)')
      ->where('poll_id = ?', $data['poll_id'])
      ->query()
      ->fetchColumn(0);
    $newData['vote_count'] = $pollVoteCount;

    // privacy
    try {

      //set privacy
      $pollPrivacy = $this->_translatePollPrivacy($data['privacy']);
      $newData['view_privacy'] = $pollPrivacy[0];

      $this->_insertPrivacy('poll', $data['poll_id'], 'view', $this->_translatePollPrivacy($data['privacy']));
      $this->_insertPrivacy('poll', $data['poll_id'], 'comment', $this->_translatePollPrivacy($data['privacy_comment']));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $data['poll_id'] . ' : ' . $e->getMessage());
    }

    // search
    if( @$newData['search'] ) {
      $this->_insertSearch('poll', @$newData['poll_id'], @$newData['title'], @$newData['description']);
    }

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_poll` (
  `poll_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `question` varchar(255) NOT NULL,
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `image_path` varchar(75) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `randomize` tinyint(1) NOT NULL DEFAULT '1',
  `hide_vote` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_id`),
  KEY `user_id` (`user_id`),
  KEY `item_id` (`item_id`,`view_id`,`privacy`),
  KEY `item_id_2` (`item_id`,`user_id`,`view_id`,`privacy`),
  KEY `item_id_3` (`item_id`,`view_id`,`question`,`privacy`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_poll_answer` (
  `answer_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int(10) unsigned NOT NULL,
  `answer` varchar(255) NOT NULL,
  `total_votes` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` tinyint(3) NOT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `poll_id` (`poll_id`),
  KEY `answer_id` (`answer_id`,`poll_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 *
 */

/*
 *
CREATE TABLE IF NOT EXISTS `phpfox_poll_result` (
  `poll_id` int(10) unsigned NOT NULL,
  `answer_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  KEY `poll_id` (`poll_id`),
  KEY `user_voted` (`poll_id`,`user_id`),
  KEY `answer_id` (`answer_id`,`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_poll_polls` (
  `poll_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(11) unsigned NOT NULL,
  `is_closed` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `vote_count` int(11) unsigned NOT NULL DEFAULT '0',
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `closed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`poll_id`),
  KEY `user_id` (`user_id`),
  KEY `is_closed` (`is_closed`),
  KEY `creation_date` (`creation_date`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_poll_options` (
  `poll_option_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) unsigned NOT NULL,
  `poll_option` text COLLATE utf8_unicode_ci NOT NULL,
  `votes` smallint(4) unsigned NOT NULL,
  PRIMARY KEY (`poll_option_id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_poll_votes` (
  `poll_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `poll_option_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  PRIMARY KEY (`poll_id`,`user_id`),
  KEY `poll_option_id` (`poll_option_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
