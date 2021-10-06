<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    Messages.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_Messages extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();
  protected $_messageCount = 0;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'mail_thread_text';
    $this->_toTable = 'engine4_messages_messages';
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

    if( is_null($data['text']) || $data['text'] == '' )
      $text = '';
    else
      $text = $data['text'];

    $userInfo = array(
      'user_id' => $data['user_id'],
      'time_stamp' => $data['time_stamp'],
      'text' => $text,
      'album_title' => 'Message Photos',
      'album_type' => 'message',
      'title' => '',
      'item_id' => $data['message_id'],
      'category_id' => 'mail'
    );
    //FIND MESSAGE BODY
    $messageArr = $this->attachOtherMessage($userInfo, '0');
    //MAKING Message ARRAY FOR INSERTION
    $newData = array();
    $newData['conversation_id'] = $data['thread_id'];
    $newData['title'] = "";
    $newData['user_id'] = $data['user_id'];
    $newData['date'] = $this->_translateTime($data['time_stamp']);
    $newData['body'] = $messageArr['body'];
    $newData['attachment_type'] = $messageArr['fAttachmentType'];
    $newData['attachment_id'] = $messageArr['fAttachmentId'];
    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_mail` (
`mail_id` int(10) unsigned NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0',
  `mass_id` int(10) unsigned NOT NULL DEFAULT '0',
  `subject` varchar(255) DEFAULT NULL,
  `preview` varchar(255) DEFAULT NULL,
  `owner_user_id` int(10) unsigned NOT NULL,
  `owner_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `owner_type_id` tinyint(1) NOT NULL DEFAULT '0',
  `viewer_user_id` int(10) unsigned NOT NULL,
  `viewer_folder_id` int(10) unsigned NOT NULL DEFAULT '0',
  `viewer_type_id` int(10) unsigned NOT NULL DEFAULT '0',
  `viewer_is_new` int(10) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `time_updated` int(10) unsigned NOT NULL DEFAULT '0',
  `total_attachment` int(10) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;
 */


/*
CREATE TABLE IF NOT EXISTS `phpfox_mail_text` (
  `mail_id` int(10) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_messages_messages` (
`message_id` int(11) unsigned NOT NULL,
  `conversation_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `body` text COLLATE utf8_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `attachment_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT '',
  `attachment_id` int(11) unsigned NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
