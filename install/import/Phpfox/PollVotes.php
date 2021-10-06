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
class Install_Import_Phpfox_PollVotes extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'poll_result';
    $this->_toTable = 'engine4_poll_votes';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _initPost()
  {
    if( !$this->_tableExists($this->getToDb(), 'engine4_poll_polls') ) {
      return false;
    }
    $indexExist = $this->getToDb()
        ->query(
          "show index from engine4_poll_votes where Key_name = 'PRIMARY';"
        )->fetch();
    if( $indexExist ) {
      $this->getToDb()
        ->query(
          "ALTER TABLE engine4_poll_votes DROP INDEX `PRIMARY`;"
      );
    }
  }

  protected function _runPost()
  {
    if( !$this->_tableExists($this->getToDb(), 'engine4_poll_polls') ) {
      return false;
    }
    $toDeleteRecords = $this->getToDb()
        ->query("select poll_id, user_id,count(*)-1 as count from engine4_poll_votes group by poll_id, user_id having count>0")->fetchAll();
    $query = "";
    if( is_array($toDeleteRecords) ) {
      foreach( $toDeleteRecords as $rc ) {
        $query = "delete FROM engine4_poll_votes where poll_id=" . $rc['poll_id'] . " and user_id=" . $rc['user_id'] . "  order by creation_date asc limit " . $rc['count'] . " ;";
        $this->getToDb()->query($query);
      }
    }
    $this->getToDb()
      ->query(
        "ALTER TABLE engine4_poll_votes ADD PRIMARY KEY (poll_id, user_id) USING BTREE;"
    );
  }

  protected function _translateRow(array $data, $key = null)
  {

    $newData = array(
      'poll_id' => $data['poll_id'],
      'user_id' => $data['user_id'],
      'poll_option_id' => $data['answer_id'],
      'creation_date' => $this->_translateTime($data['time_stamp']),
      'modified_date' => $this->_translateTime($data['time_stamp']),
    );
    return $newData;
  }
}

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
