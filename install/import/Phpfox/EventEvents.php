<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    EventEvents.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_EventEvents extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'event';
    $this->_toTable = 'engine4_event_events';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
    $prefix = $this->getfromPrefix();
    $picSize = $this->getFromDb()
      ->select()
      ->from($prefix . "setting", 'value_actual')
      ->where('var_name = ?', 'photo_pic_sizes')
      ->query()
      ->fetchColumn(0);
    $this->_fileSize = null;
    if( $picSize !== false ) {
      $picSize = explode(':"', $picSize);
      if( isset($picSize[1]) ) {
        $picSize = explode(';";', $picSize[1]);
        if( isset($picSize[0]) ) {
          $picSize = $picSize[0];
          $picSizeArr = $this->getStringBetween($picSize, "=>", ",");
          foreach( $picSizeArr as $ps ) {
            $ps = str_replace("'", "", $ps);
            $dt[] = (int) $ps;
          }
          rsort($dt);
          if( isset($dt[0]) )
            $this->_fileSize = $dt[0];
        }
      }
    }
  }

  //protected $_fromWhere = array('module_id=?' => 'event');

  protected function _translateRow(array $data, $key = null)
  {
    $parent_type = 'user';
    $parent_id = $data['user_id'];
    $type_id = null;
    //GET TYPE ID
    if( $data['module_id'] == 'pages' ) {
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'pages', 'type_id')
        ->where('page_id = ?', $data['item_id'])
        ->query()
        ->fetchColumn();
      $parent_type = 'group';
      $parent_id = $data['item_id'];
    }

    //GET CATGEORY ID
    $category_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'event_category_data', 'category_id')
      ->where('event_id = ?', $data['event_id'])
      ->query()
      ->fetchColumn();

    //GET DESCRIPTION
    $description = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'event_text', 'description_parsed')
      ->where('event_id = ?', $data['event_id'])
      ->query()
      ->fetchColumn();

    //MAKING EVENT ARRAY FOR INSERTION
    $newData = array();
    $newData['event_id'] = $data['event_id'];
    $newData['title'] = $data['title'];
    $newData['description'] = $description ? $description : '';
    $newData['category_id'] = $category_id;
    $newData['user_id'] = $data['user_id'];
    $newData['parent_type'] = $parent_type;
    $newData['parent_id'] = $parent_id;
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['starttime'] = $this->_translateTime($data['start_time']);
    $newData['endtime'] = $this->_translateTime($data['end_time']);
    $newData['location'] = $data['location'];
    //$newData['view_count'] = $data['total_comment'];

    if( $type_id ) {
      //GETTING THE PRIVACY
      $perms = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'pages_perm', '*')
        ->where('page_id = ?', $data['item_id'])
        ->query()
        ->fetchAll();

      $viewPrivacy = 0;
      foreach( $perms as $permissions ) {
        switch( $permissions['var_name'] ) {
          case 'event.view_browse_events':
            $viewPrivacy = $permissions['var_value'];
            break;
        }
      }

      //set privacy
      $eventPrivacy = $this->_translateParentEventPrivacy($viewPrivacy);
      $newData['view_privacy'] = $eventPrivacy[0];

      $this->_insertPrivacy('event', $data['event_id'], 'view', $this->_translateParentEventPrivacy($viewPrivacy));
    } else {

      //set privacy
      $eventPrivacy = $this->_translateEventPrivacy($data['privacy']);
      $newData['view_privacy'] = $eventPrivacy[0];

      $this->_insertPrivacy('event', $data['event_id'], 'view', $this->_translateEventPrivacy($data['privacy']));
    }
    // privacy
    try {

      $this->_insertPrivacy('event', $data['event_id'], 'comment', $this->_translateEventPrivacy($data['privacy_comment']));
      $this->_insertPrivacy('event', $data['event_id'], 'photo', $this->_translateEventPrivacy(0));
      $this->_insertPrivacy('event', $data['event_id'], 'invite', $this->_translateEventPrivacy(5));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $data['event_id'] . ' : ' . $e->getMessage());
    }

    //GET DESTINATION AND INSERT THE EVENT MAIN PHOTO
    if( !empty($data['image_path']) ) {
      $phDest = $this->getStringBetween($data['image_path'], '{', '}');
      if( !empty($phDest) && count($phDest) > 0 )
        $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . $phDest[0];
      else {
        $srcPath = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/event' . DIRECTORY_SEPARATOR . $data['image_path'];
      }
      $des = explode('%s', $srcPath);
      $file = $des[0];
      if( isset($des[1]) )
        $file = $des[0] . $des[1];
      if( $file ) {
        try {
          $fileInfo = array(
            'parent_type' => 'event',
            'parent_id' => $data['event_id'],
            'user_id' => @$data['user_id'],
          );
          //CREATING THE FILE FOR MAIN PHOTO
          if( $this->getParam('resizePhotos', true) ) {
            $file_id = $this->_translatePhoto($file, $fileInfo);
          } else {
            $file_id = $this->_translateFile($file, $fileInfo, true);
          }
          if( empty($file_id) ) {
            $file_id = $this->createFileDiffSize($file, $fileInfo, $this->_fileSize);
          }
        } catch( Exception $e ) {
          $file_id = null;
          $this->_logFile($e->getMessage());
        }
        if( empty($file_id) ) {
          $this->_logFile("Event main photo not found . Photo path " . $file);
        }
        if( $file_id ) {
          $newData['photo_id'] = $file_id;
        }
        //CREATING THE ALBUM FOR EVENT
        $this->getToDb()->insert('engine4_event_albums', array(
          'event_id' => $data['event_id'],
          'creation_date' => $this->_translateTime($data['time_stamp']),
          'modified_date' => $this->_translateTime($data['time_stamp']),
          'collectible_count' => 1
        ));

        $album_id = $this->getToDb()->lastInsertId();
        //SEARCH
        $this->_insertSearch('event_album', $album_id, '', '');
        try {
          $fileInfo = array(
            'parent_type' => 'event_photo',
            'parent_id' => $album_id,
            'user_id' => @$data['user_id'],
          );
          if( $this->getParam('resizePhotos', true) ) {
            $filephoto_id = $this->_translatePhoto($file, $fileInfo);
          } else {
            $filephoto_id = $this->_translateFile($file, $fileInfo, true);
          }
          if( empty($filephoto_id) ) {
            $filephoto_id = $this->createFileDiffSize($file, $fileInfo, $this->_fileSize);
          }
        } catch( Exception $e ) {
          $filephoto_id = null;
          $this->_logFile($e->getMessage());
        }
        if( !empty($filephoto_id) ) {
          //INSERT THE EVENT PHOTO
          $this->getToDb()->insert('engine4_event_photos', array(
            'album_id' => $album_id,
            'collection_id' => $album_id,
            'event_id' => $data['event_id'],
            'creation_date' => $this->_translateTime($data['time_stamp']),
            'modified_date' => $this->_translateTime($data['time_stamp']),
            'user_id' => @$data['user_id'],
            'file_id' => $filephoto_id
          ));
          $photo_id = $this->getToDb()->lastInsertId();
          $this->_insertSearch('event_photo', $photo_id, '', '');
        } else {
          $this->_logFile("Event main photo not found . Photo path " . $file);
        }
      }
    }
    $newData['search'] = 1;
    //SEARCH
    if( @$newData['search'] ) {
      $this->_insertSearch('event', @$newData['event_id'], @$newData['title'], @$newData['description']);
    }

    //GET INVITES
    $invites = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'event_invite', '*')
      ->where('event_id = ?', $data['event_id'])
      ->query()
      ->fetchAll();
    $count = count($invites);
    $active = 1;
    $resource_approved = 1;
    $user_approved = 1;
    foreach( $invites as $invite ) {
      if( $invite['rsvp_id'] == 1 ) {
        $rsvp = 2;
      } else if( $invite['rsvp_id'] == 2 ) {
        $rsvp = 1;
      } else if( $invite['rsvp_id'] == 3 ) {
        $rsvp = 0;
      } else if( $invite['rsvp_id'] == 0 ) {
        $rsvp = 3;
        $active = 0;
        $user_approved = 0;
      }
      //INSERT ALL THE MEMBER OF THIS EVENT
      $this->getToDb()->insert('engine4_event_membership', array(
        'resource_id' => $invite['event_id'],
        'user_id' => $invite['invited_user_id'],
        'active' => $active,
        'resource_approved' => $resource_approved,
        'user_approved' => $user_approved,
        'rsvp' => $rsvp
      ));
    }
    $newData['member_count'] = $count;

    return $newData;
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_event` (
  `event_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `module_id` varchar(75) NOT NULL DEFAULT 'event',
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `country_iso` char(2) DEFAULT NULL,
  `country_child_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `postal_code` varchar(20) DEFAULT NULL,
  `city` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `start_time` int(10) unsigned NOT NULL,
  `end_time` int(10) unsigned NOT NULL,
  `image_path` varchar(75) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `mass_email` int(10) unsigned NOT NULL DEFAULT '0',
  `start_gmt_offset` varchar(15) DEFAULT NULL,
  `end_gmt_offset` varchar(15) DEFAULT NULL,
  `gmap` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`event_id`),
  KEY `module_id` (`module_id`,`item_id`),
  KEY `user_id` (`user_id`),
  KEY `view_id` (`view_id`,`privacy`,`item_id`,`start_time`),
  KEY `view_id_2` (`view_id`,`privacy`,`item_id`,`user_id`,`start_time`),
  KEY `view_id_3` (`view_id`,`privacy`,`user_id`),
  KEY `view_id_4` (`view_id`,`privacy`,`item_id`,`title`),
  KEY `view_id_5` (`view_id`,`privacy`,`module_id`,`item_id`,`start_time`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_event_text` (
  `event_id` int(10) unsigned NOT NULL,
  `description` mediumtext,
  `description_parsed` mediumtext,
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_event_category_data` (
  `event_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `category_id` (`category_id`),
  KEY `event_id` (`event_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_event_invite` (
  `invite_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(10) unsigned NOT NULL,
  `type_id` tinyint(1) NOT NULL DEFAULT '0',
  `rsvp_id` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `invited_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `invited_email` varchar(100) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`invite_id`),
  KEY `event_id` (`event_id`),
  KEY `event_id_2` (`event_id`,`invited_user_id`),
  KEY `invited_user_id` (`invited_user_id`),
  KEY `event_id_3` (`event_id`,`rsvp_id`,`invited_user_id`),
  KEY `rsvp_id` (`rsvp_id`,`invited_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_event_events` (
  `event_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `parent_type` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(11) unsigned NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `starttime` datetime NOT NULL,
  `endtime` datetime NOT NULL,
  `host` varchar(115) COLLATE utf8_unicode_ci NOT NULL,
  `location` varchar(115) COLLATE utf8_unicode_ci NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `member_count` int(11) unsigned NOT NULL DEFAULT '0',
  `approval` tinyint(1) NOT NULL DEFAULT '0',
  `invite` tinyint(1) NOT NULL DEFAULT '0',
  `photo_id` int(11) unsigned NOT NULL,
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`event_id`),
  KEY `user_id` (`user_id`),
  KEY `parent_type` (`parent_type`,`parent_id`),
  KEY `starttime` (`starttime`),
  KEY `search` (`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_event_membership` (
  `resource_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `resource_approved` tinyint(1) NOT NULL DEFAULT '0',
  `user_approved` tinyint(1) NOT NULL DEFAULT '0',
  `message` text COLLATE utf8_unicode_ci,
  `rsvp` tinyint(3) NOT NULL DEFAULT '3',
  `title` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`resource_id`,`user_id`),
  KEY `REVERSE` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_event_albums` (
  `album_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `event_id` int(11) unsigned NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `collectible_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`album_id`),
  KEY `event_id` (`event_id`),
  KEY `search` (`search`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 *
CREATE TABLE IF NOT EXISTS `engine4_event_photos` (
  `photo_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(11) unsigned NOT NULL,
  `event_id` int(11) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `title` varchar(128) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `collection_id` int(11) unsigned NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`),
  KEY `event_id` (`event_id`),
  KEY `collection_id` (`collection_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
