<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    MessagesConversations.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_MessagesRecipients extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_priority = 97;
  protected $_warningMessage = array();
  protected $_messageCount = 0;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'mail_thread_user';
    $this->_toTable = 'engine4_messages_recipients';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
    $msgCount = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'mail_thread_text', 'count(*)')
      ->query()
      ->fetchColumn(0);
    $this->_messageCount = $msgCount;
  }

  protected function _translateRow(array $data, $key = null)
  {

    if( $this->_messageCount == 0 )
      return false;
    //MAKING Message Conversation ARRAY FOR INSERTION
    $newData = array
      (
      'user_id' => $data['user_id'],
      'conversation_id' => $data['thread_id'],
      'inbox_read' => $data['is_read'],
    );

    //FIND LATEST SENT MESSAGE AND TIME BY  THIS USER
    $outbox = $this->getFromDb()->select()->from($this->getfromPrefix() . 'mail_thread_text', array('max(message_id) as id', 'time_stamp'))
      ->where('thread_id = ?', $data['thread_id'])
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetch();
    $outboxId = 0;
    $outboxDate = null;
    if( $outbox ) {
      $outboxId = $outbox['id'];
      $outboxDate = $this->_translateTime($outbox['time_stamp']);
    }

    //FIND LATEST RECEIVE MESSAGE AND TIME BY  THIS USER
    $inbox = $this->getFromDb()->select()->from($this->getfromPrefix() . 'mail_thread_text', array('max(message_id) as id', 'time_stamp'))
      ->where('thread_id = ?', $data['thread_id'])
      ->where('user_id <> ?', $data['user_id'])
      ->query()
      ->fetch();
    $inboxId = 0;
    $inboxDate = null;
    if( $inbox ) {
      $inboxId = $inbox['id'];
      $inboxDate = $this->_translateTime($inbox['time_stamp']);
    }


    if( $data['is_sent'] ) {
      $newData['inbox_deleted'] = 1;
      $newData['outbox_deleted'] = 0;
      if( $inboxId != 0 ) {
        $newData['inbox_deleted'] = 0;
      }
    } else {
      $newData['inbox_deleted'] = 0;
      $newData['outbox_deleted'] = 1;
      if( $outboxId != 0 ) {
        $newData['outbox_deleted'] = 0;
      }
    }
    $newData['inbox_message_id'] = $inboxId;
    $newData['inbox_updated'] = $inboxDate;
    $newData['outbox_message_id'] = $outboxId;
    $newData['outbox_updated'] = $outboxDate;
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_user_blocked` (
  `block_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `block_user_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `ip_address` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`block_id`),
  KEY `user_id` (`user_id`,`block_user_id`),
  KEY `user_id_2` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_user_block` (
  `user_id` int(11) unsigned NOT NULL,
  `blocked_user_id` int(11) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`blocked_user_id`),
  KEY `REVERSE` (`blocked_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
