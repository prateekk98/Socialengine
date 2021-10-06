<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    ChatWhispers.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_ChatWhispers extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'im_text';
    $this->_toTable = 'engine4_chat_whispers';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //FIND RECIPIENT ID 
    $recipientId = $this->getRecipientId($data['parent_id'], $data['user_id']);
    //PREPARING AN ARRAY FOR CHAT WHISPERS
    $newData = array();
    $newData['sender_id'] = $data['user_id'];
    $newData['body'] = $data['text'];
    $newData['date'] = $this->_translateTime($data['time_stamp']);
    $newData['recipient_deleted'] = 0;
    $newData['sender_deleted'] = 0;
    $newData['recipient_id'] = $recipientId;
    return $newData;
  }
  /*
   * RETURN THE RECIPIENT ID
   */

  public function getRecipientId($imId, $senderId)
  {
    $receiverId = 0;
    $imModel = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'im', '*')
      ->where('im_id = ?', $imId)
      ->limit(1)
      ->query()
      ->fetch();
    if( !$imModel )
      return $receiverId;
    $receiverId = ($senderId != $imModel['user_id']) ? $imModel['user_id'] : $imModel['owner_user_id'];
    return $receiverId;
  }
}

/*
 
CREATE TABLE IF NOT EXISTS `phpfox_im_text` (
`text_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `text` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

 */

/*
 CREATE TABLE IF NOT EXISTS `engine4_chat_whispers` (
`whisper_id` bigint(20) NOT NULL,
  `recipient_id` int(11) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `recipient_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `sender_deleted` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

 */
