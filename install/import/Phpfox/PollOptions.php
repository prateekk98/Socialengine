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
class Install_Import_Phpfox_PollOptions extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();
  protected $_fromOrderBy = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'poll_answer';
    $this->_toTable = 'engine4_poll_options';
    $this->_fromOrderBy = array(array('answer_id', 'ordering'), 'ASC');
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    $newData = array(
      'poll_option_id' => $data['answer_id'],
      'poll_id' => $data['poll_id'],
      'poll_option' => $data['answer'],
      'votes' => $data['total_votes'],
    );
    return $newData;
  }
}

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
 * CREATE TABLE IF NOT EXISTS `engine4_poll_options` (
  `poll_option_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `poll_id` int(11) unsigned NOT NULL,
  `poll_option` text COLLATE utf8_unicode_ci NOT NULL,
  `votes` smallint(4) unsigned NOT NULL,
  PRIMARY KEY (`poll_option_id`),
  KEY `poll_id` (`poll_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */
