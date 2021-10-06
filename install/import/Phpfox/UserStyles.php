<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserStyles.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserStyles extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'user_css_code';
    $this->_toTable = 'engine4_core_styles';
  }

  protected function _translateRow(array $data, $key = null)
  {
    //MAKING ARRAY FOR INSERTION OF USER CSS CODE 
    $newData = array();
    $newData['type'] = 'user';
    $newData['id'] = $data['user_id'];
    $newData['style'] = $data['css_code'];

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_user_css_code` (
  `user_id` int(10) unsigned NOT NULL,
  `css_code` mediumtext,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_core_styles` (
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `style` text COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`type`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */
