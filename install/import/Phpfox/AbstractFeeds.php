<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractFeeds.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractFeeds extends Install_Import_Phpfox_Abstract
{

  protected $_toTableTruncate = true;
  protected $_fromResourceType;
  protected $_toResourceType;

  /* Moved to CleanupPre
    static protected $_toTableTruncated = false;
   */
  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_fromResourceType', '_toResourceType' //, '_toTableTruncated', // That last one might not work
    ));
  }

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'feed';
    $this->_toTable = 'engine4_activity_actions';
    $this->_truncateTable($this->getToDb(), 'engine4_activity_stream');
    $this->_truncateTable($this->getToDb(), 'engine4_activity_attachments');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET FROM RESOURCE TYPE
    $objectType = $this->_fromResourceType;

    //GET TO RESOURCE TYPE
    $toType = $this->_toResourceType;
    $attachmentType = '';
    //INTIALISE VARIABLES
    $newData = array();
    $body = '';
    $type_id = '';
    $link = '';
    $tempLink = '';
    $tempData = $data['item_id'];
    $mode = 1;
    $isAttachment = true;
    $isPostedAsPage = false;
    $userId = $this->getFromDb()->query("SELECT  " . $this->getfromPrefix() . "pages.user_id FROM " . $this->getfromPrefix() . "user left join  " . $this->getfromPrefix() . "pages on page_id=profile_page_id where profile_page_id<>0 and  " . $this->getfromPrefix() . "user.user_id=" . $data['user_id'])
      ->fetchColumn(0);
    if( $userId ) {
      $data['user_id'] = $userId;
      $isPostedAsPage = true;
    }

    $totalFeeds = 0;
    $totalFeeds += (int) $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'event_feed', 'COUNT(*)')
      ->query()
      ->fetchColumn(0);

    $totalFeeds += (int) $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'pages_feed', 'COUNT(*)')
      ->query()
      ->fetchColumn(0);

    $actionId = $data['feed_id'] + $totalFeeds;

    //CHECK FOR GROUP [WE ARE SKIPPING THE COMMENTS FOR GROUP FOR BLOG, VIDEO]
    //FIND ATTACHMENT TYPE,OBJECT TYPE AND ITEM ID ON DIFFERENT CONDITION
    if( $toType == 'Poke' ) {
      return false;
    } else if( $objectType == 'blog' ) {
      //FIND PAGE INFO
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'blog', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'blog.item_id', array('type_id', $this->getfromPrefix() . 'pages.page_id'))
        ->where($this->getfromPrefix() . 'blog.blog_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'blog.module_id = ?', 'pages')
        ->query()
        ->fetch();
      if( $pageInfo ) {
        return false;
      }
    } else if( $objectType == 'video' ) {

      //FIND PAGE INFO
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'video', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'video.item_id', array('type_id', $this->getfromPrefix() . 'pages.page_id'))
        ->where($this->getfromPrefix() . 'video.video_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'video.module_id = ?', 'pages')
        ->query()
        ->fetch();
      if( !empty($pageInfo) ) {
        return false;
      }
    } else if( $objectType == 'music_playlist' ) {
      $pageInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_album', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'music_album.item_id', array('type_id', $this->getfromPrefix() . 'pages.page_id'))
        ->where($this->getfromPrefix() . 'music_album.album_id = ?', $data['item_id'])
        ->where($this->getfromPrefix() . 'music_album.module_id = ?', 'pages')
        ->query()
        ->fetch();
      if( !empty($pageInfo) ) {
        return false;
      }
    } else if( $objectType == 'music_playlist_song' ) {
      $type_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_song', null)
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'music_song.item_id', 'type_id')
        ->where($this->getfromPrefix() . 'music_song.song_id = ?', $data['item_id'])
        ->query()
        ->fetchColumn();

      if( $type_id == 3 ) {
        return false;
      } else {
        if( $data['item_id'] != 0 ) {
          $toType = 'music_playlist_new';
        }
      }
    }
    //CHECK FOR STATUS
    if( $toType == 'status' ) {

      //CHECK PARENT FEED MODULE AND ID
      if( $data['parent_module_id'] && $data['parent_feed_id'] ) {

        //CHANGE THE TYPE TO SHARE
        $toType = 'share';
        $tempData = $data['parent_feed_id'];
      }

      //GET BODY
      $body = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'user_status', 'content')
        ->where('status_id = ?', $data['item_id'])
        ->where('user_id = ?', $data['user_id'])
        ->query()
        ->fetchColumn();
      $objectType = 'user';
      $data['item_id'] = $data['user_id'];
    } else if( $toType == 'post_self' && $objectType == 'user' ) {

      //FIND PHOTO DETAIL
      $photoDtl = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo', array('group_id', 'time_stamp'))
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'photo.group_id', $this->getfromPrefix() . 'pages.type_id')
        ->where('photo_id= ?', $data['item_id'])
        ->where('module_id= ?', 'pages')
        ->query()
        ->fetch();

      if( $photoDtl ) {
        return false;
      } else {
        $type_id = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'photo', 'type_id')
          ->where('type_id = ?', 1)
          ->where('photo_id = ?', $data['item_id'])
          ->query()
          ->fetchColumn();

        if( !$type_id )
          return false;
        $data['item_id'] = $data['user_id'];
      }
      $description = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo_info', 'description')
        ->where('photo_id = ?', $tempData)
        ->query()
        ->fetchColumn();

      if( $description ) {
        //UPDATE ALBUM DESC
        $this->getToDb()->update('engine4_album_photos', array(
          'description' => $description,
          'title' => '',
          ), array(
          'photo_id = ?' => $tempData,
        ));
      }
    } else if( $toType == 'post_self' && $objectType == 'core_link' ) {

      $linkDtl = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'link', array('status_info', 'item_id'))
        ->join($this->getfromPrefix() . 'pages', $this->getfromPrefix() . 'pages.page_id=' . $this->getfromPrefix() . 'link.item_id', $this->getfromPrefix() . 'pages.type_id')
        ->where('link_id= ?', $tempData)
        ->where('module_id= ?', 'pages')
        ->query()
        ->fetch();
      if( $linkDtl ) {
        return false;
      } else {
        $data['item_id'] = $data['user_id'];
        $tempLink = $objectType;
        $objectType = 'user';
        $link = true;
        $body = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'link', 'status_info')
          ->where('link_id=?', $tempData)
          ->query()
          ->fetchColumn();
      }
    } elseif( $toType == 'music_playlist_new' ) {
      $album = $this->getFromDb()->select()
        ->from($this->getFromPrefix() . 'music_song')
        ->where('song_id=?', $data['item_id'])
        ->query()
        ->fetch();

      if( !$album ) {
        return false;
      }

      if( empty($album['album_id']) ) {
        $playlistData = array();
        $playlistData['title'] = $album['title'];
        $playlistData['description'] = '';
        $playlistData['owner_type'] = 'user';
        $playlistData['search'] = 1;
        $playlistData['owner_id'] = $data['user_id'];
        $playlistData['creation_date'] = $this->_translateTime(time());
        $playlistData['modified_date'] = $this->_translateTime(time());

        $this->getToDb()->insert('engine4_music_playlists', $playlistData);

        $album['album_id'] = $this->getToDb()->lastInsertId();

        $songsData = array();
        $songsData['song_id'] = $album['song_id'];
        $songsData['playlist_id'] = $album['album_id'];
        $songsData['title'] = $album['title'];
        $songsData['play_count'] = $album['total_play'];
        $songsData['order'] = $album['ordering'];

        if( $album['song_path'] ) {
          $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/music' . DIRECTORY_SEPARATOR . $album['song_path']);
          $file = $des[0];
          if( isset($des[1]) ) {
            $file = $des[0] . $des[1];
          }

          if( $file ) {
            try {
              $fileId = $this->_translateFile($file, array(
                'parent_type' => 'music_playlist_song',
                'parent_id' => $album['album_id']
              ), true);
            } catch( Exception $e ) {
              $fileId = null;
              $this->_warning($e->getMessage(), 1);
            }

            $songsData['file_id'] = $fileId;
          }
        }

        $this->getToDb()->insert('engine4_music_playlist_songs', $songsData);
        $this->_insertSearch('music_playlist_song', $album['song_id'], $songsData['title']);
      }

      $data['item_id'] = $album['album_id'];
      $tempData = $album['album_id'];
    } else if( $toType == 'post_self' && $objectType == 'music_playlist_song' ) {
      $data['item_id'] = $data['user_id'];
      $tempLink = $objectType;
      $objectType = 'user';
      $link = true;
      $body = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_song', 'description')
        ->where('song_id=?', $tempData)
        ->query()
        ->fetchColumn();
    } else if( $objectType == 'forum_topic' && $toType == 'forum_topic_reply' ) {
      $tempData = $data['item_id'];
      $data['item_id'] = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'forum_post', 'thread_id')
        ->where('post_id=?', $data['item_id'])
        ->query()
        ->fetchColumn();
    } else if( $objectType == 'user' && $toType == 'profile_photo_update' ) {
      $type_id = true;
      $tempData = $data['item_id'];
      $data['item_id'] = $data['user_id'];
    } elseif( $toType == 'album_photo_new' && $objectType == 'album' ) {
      $album_id = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo', 'album_id')
        ->where('type_id = ?', 0)
        ->where('module_id is NULL')
        ->where('photo_id = ?', $data['item_id'])
        ->query()
        ->fetchColumn();

      if( !$album_id )
        return false;
      $data['item_id'] = $album_id;

      $count = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo_feed', array('count' => 'COUNT(*)'))
        ->where('feed_id = ?', $data['feed_id'])
        ->query()
        ->fetchColumn();

      $newData['params'] = '{"count":"' . (int) ($count + 1) . '"}';
      $description = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo_info', 'description')
        ->where('photo_id = ?', $tempData)
        ->query()
        ->fetchColumn();

      if( $description ) {
        $this->getToDb()->update('engine4_album_photos', array(
          'description' => $description,
          'title' => '',
          ), array(
          'photo_id = ?' => $tempData,
        ));
      }
    } else if( $objectType == 'user' && $toType == 'tagged' ) {
      $newData = array_merge($newData, array(
        'params' => '{"label":"photo"}',
      ));
      if( $data['parent_user_id'] == $data['user_id'] )
        return false;
      $type_id = true;
      $tempData = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo_tag', 'photo_id')
        ->where('tag_id = ?', $data['item_id'])
        ->query()
        ->fetchColumn();

      $data['item_id'] = $data['parent_user_id'];
    } elseif( $toType == 'post_self' && $objectType == 'PHPfox_Videos' ) {
      $objectType = 'user';
      $isAttachment = true;
      $attachmentType = 'video';
      $data['item_id'] = $data['user_id'];
      $tempData = $this->getToDb()->select()
        ->from('engine4_video_videos', 'video_id')
        ->where('parent_id = ?', $data['feed_id'])
        ->query()
        ->fetchColumn();

      // $this->_log('videos: ' . print_r($tempData, true));
    }

    //MAKING NEW DATA ARRAY FOT INSERTION
    $newData = array_merge($newData, array(
      'action_id' => $actionId,
      'type' => $toType,
      'subject_type' => 'user',
      'subject_id' => $data['user_id'],
      'object_type' => $objectType,
      'object_id' => $data['item_id'],
      'body' => $body,
      'date' => $this->_translateTime($data['time_stamp']),
      'attachment_count' => 1
    ));

    $privacy_field = $this->getToDb()->query("SHOW COLUMNS FROM engine4_activity_actions LIKE 'privacy'")->fetch();
    $privacy = 'everyone';
    if( !empty($privacy_field) ) {
      switch( $data['privacy'] ) {
        case '1':
        case '2':
          $privacy = 'friends';
          break;
        case '3':
          $privacy = 'onlyme';
          break;
        default: $privacy = 'everyone';
      }
      $newData = array_merge($newData, array('privacy' => $privacy));
    }

    //INTIALISING THE TARGET TYPES
    $targetTypes = array();
    if( $objectType != 'user' ) {
      //GET PRIVACY
      try {
        if( empty($objectType) || empty($data['item_id']) )
          return false;
        $privacyTypes = $this->getToDb()->select()
          ->from('engine4_authorization_allow', 'role')
          ->where('resource_type = ?', $objectType)
          ->where('resource_id = ?', $data['item_id'])
          ->where('action = ?', 'view')
          ->query()
          ->fetchAll(Zend_Db::FETCH_COLUMN);
      } catch( Exception $ex ) {
        
      }
      if( empty($privacyTypes) ) {
        $targetTypes['everyone'] = 0;
        $targetTypes['owner'] = $data['user_id'];
        if( $toType == 'forum_topic_create' || $toType == 'forum_topic_reply' ) {
          $forum_id = $this->getFromDb()->select()
            ->from($this->getfromPrefix() . 'forum_thread', 'forum_id')
            ->where('thread_id=?', $data['item_id'])
            ->query()
            ->fetchColumn();
          $targetTypes['forum'] = $forum_id;
        } else {
          $targetTypes['registered'] = 0;
          $targetTypes['parent'] = $data['user_id'];
          $targetTypes['members'] = $data['user_id'];
        }
      } else {
        if( in_array('everyone', $privacyTypes) && in_array('owner_member', $privacyTypes) && in_array('owner_member_member', $privacyTypes) && in_array('owner_network', $privacyTypes) ) {
          $targetTypes['everyone'] = 0;
          $targetTypes['registered'] = 0;
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
          $targetTypes['members'] = $data['user_id'];
        } else if( in_array('owner_member', $privacyTypes) && in_array('owner_member_member', $privacyTypes) && in_array('owner_network', $privacyTypes) ) {
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
          $targetTypes['members'] = $data['user_id'];
        } else if( in_array('owner_member', $privacyTypes) && in_array('owner_member_member', $privacyTypes) ) {
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
          $targetTypes['members'] = $data['user_id'];
        } else if( in_array('owner_member', $privacyTypes) ) {
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
        }
      }
    } else {
      switch( $privacy ) {
        case 'friends':
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
          $targetTypes['members'] = $data['user_id'];
          break;
        case 'onlyme':
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
          break;
        case 'everyone':
          $targetTypes['everyone'] = 0;
          $targetTypes['registered'] = 0;
          $targetTypes['owner'] = $data['user_id'];
          $targetTypes['parent'] = $data['user_id'];
          $targetTypes['members'] = $data['user_id'];
          break;
      }

      if( $objectType == 'user' && $toType == 'tagged' ) {
        $targetTypes['parent'] = $data['parent_user_id'];
        $targetTypes['members'] = $data['parent_user_id'];
      }
    }

    //INSERT INTO STREAM TABLE
    foreach( $targetTypes as $targetType => $targetIdentity ) {
      $insert = array(
        'target_type' => $targetType,
        'target_id' => $targetIdentity,
        'subject_type' => 'user',
        'subject_id' => $data['user_id'],
        'object_type' => $objectType,
        'object_id' => $data['item_id'],
        'type' => $toType,
        'action_id' => $actionId,
      );
      try {
        $this->getToDb()->insert('engine4_activity_stream', $insert);
      } catch( Exception $e ) {
        $this->_error("Problem adding activity privacy: " . $e->getMessage() . "\nQuery: " . print_r($insert, true));
      }
    }

    //CHANGE OBJECT TYPE IN CASE OF ALBUM
    $objectType = $type_id ? 'album_photo' : $objectType;

    //CHANGE OBJECT TYPE IN CASE OF LINK 
    if( $link ) {
      $objectType = $tempLink;
    }

    //CHANGE OBJECT TYPE IN CASE OF FORUM
    if( $objectType == 'forum_topic' && $toType == 'forum_topic_reply' ) {
      $objectType = 'forum_post';
    }

    //CHECK THE RESOURCE TYPE IF THIS IS SHARE THEN WE ARE SETTING THE PARAMS
    if( $toType == 'share' ) {
      $newData = array_merge($newData, array(
        'params' => '{"type":"item"}',
      ));
      if( $data['parent_module_id'] && $data['parent_feed_id'] ) {
        if( $data['parent_module_id'] == 'music_song' ) {
          $objectType = 'music_playlist_song';
        } elseif( $data['parent_module_id'] == 'marketplace' ) {
          $objectType = 'classified';
        } else {
          $objectType = $data['parent_module_id'];
        }
      }
    }

    //RESOURCE TYPE FOR ALBUM AND INSERTING THE ATTACHMENTS
    if( !$isAttachment ) {
      // Escaping attachment
    } else if( $toType == 'album_photo_new' && $objectType == 'album' ) {
      //PREPARING AN ARRAY TO INSERT ATTACHMENT
      $newAttachmentData = array(
        'action_id' => $actionId,
        'type' => 'album_photo',
        'id' => $tempData,
        'mode' => 1
      );
      //INSERT ATTACHMENT
      $this->getToDb()->insert('engine4_activity_attachments', $newAttachmentData);
      $photoIds = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'photo_feed', 'photo_id')
        ->where('feed_id = ?', $data['feed_id'])
        ->query()
        ->fetchAll(Zend_Db::FETCH_COLUMN);
      $newAttachmentData = array();
      //INSERT ATTACHMENT
      foreach( $photoIds as $photoId ) {
        $newAttachmentData = array(
          'action_id' => $data['feed_id'],
          'type' => 'album_photo',
          'id' => $photoId,
          'mode' => 1
        );
        $this->getToDb()->insert('engine4_activity_attachments', $newAttachmentData);
      }
    } else if( $toType != 'status' ) {
      if( $attachmentType == '' )
        $attachmentType = $objectType;
      //PREPARING AN ARRAY TO INSERT ATTACHMENT
      $newAttachmentData = array(
        'action_id' => $actionId,
        'type' => $attachmentType,
        'id' => $tempData,
        'mode' => $mode
      );
      //INSERT ATTACHMENT
      $this->getToDb()->insert('engine4_activity_attachments', $newAttachmentData);
    }

    return $newData;
  }

}

/*
 * 
CREATE TABLE IF NOT EXISTS `phpfox_feed` (
  `feed_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `app_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `type_id` varchar(75) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `item_id` int(10) unsigned NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `feed_reference` int(10) NOT NULL DEFAULT '0',
  `parent_feed_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_module_id` varchar(75) DEFAULT NULL,
  `time_update` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`feed_id`),
  KEY `privacy_2` (`privacy`,`time_stamp`,`feed_reference`),
  KEY `privacy_3` (`privacy`,`user_id`,`feed_reference`),
  KEY `privacy_4` (`privacy`,`parent_user_id`,`feed_reference`),
  KEY `type_id` (`type_id`,`item_id`,`feed_reference`),
  KEY `privacy` (`privacy`,`user_id`,`time_stamp`,`feed_reference`),
  KEY `time_stamp` (`time_stamp`,`feed_reference`),
  KEY `time_update` (`time_update`),
  KEY `privacy_5` (`privacy`,`parent_user_id`),
  KEY `user_id` (`user_id`,`feed_reference`,`time_stamp`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

 
/*
 * CREATE TABLE IF NOT EXISTS `phpfox_user_status` (
  `status_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `privacy` tinyint(3) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(3) NOT NULL DEFAULT '0',
  `content` mediumtext,
  `time_stamp` int(10) unsigned NOT NULL,
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `location_latlng` varchar(100) DEFAULT NULL,
  `location_name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`status_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo` (
  `photo_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `album_id` int(10) unsigned NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `module_id` varchar(75) DEFAULT NULL,
  `group_id` int(10) unsigned NOT NULL DEFAULT '0',
  `type_id` tinyint(3) NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `destination` varchar(255) DEFAULT NULL,
  `server_id` tinyint(3) NOT NULL,
  `mature` tinyint(1) NOT NULL DEFAULT '0',
  `allow_comment` tinyint(1) NOT NULL DEFAULT '0',
  `allow_rate` tinyint(1) NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_download` int(10) unsigned NOT NULL DEFAULT '0',
  `total_rating` decimal(3,2) NOT NULL DEFAULT '0.00',
  `total_vote` int(10) unsigned NOT NULL DEFAULT '0',
  `total_battle` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_cover` tinyint(1) NOT NULL DEFAULT '0',
  `allow_download` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `ordering` int(10) unsigned NOT NULL DEFAULT '0',
  `is_profile_photo` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`photo_id`),
  KEY `album_id` (`album_id`,`view_id`),
  KEY `photo_id` (`photo_id`,`album_id`,`view_id`,`group_id`,`privacy`),
  KEY `view_id_2` (`view_id`,`group_id`,`type_id`,`privacy`),
  KEY `photo_id_2` (`photo_id`,`album_id`,`view_id`,`group_id`,`type_id`,`privacy`),
  KEY `view_id` (`view_id`),
  KEY `privacy` (`privacy`,`allow_rate`),
  KEY `view_id_3` (`view_id`,`group_id`,`type_id`,`user_id`),
  KEY `album_id_2` (`album_id`,`view_id`,`is_cover`),
  KEY `view_id_4` (`view_id`,`privacy`,`title`),
  KEY `view_id_5` (`view_id`,`module_id`,`group_id`,`privacy`),
  KEY `is_profile_photo` (`is_profile_photo`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * 
CREATE TABLE IF NOT EXISTS `phpfox_photo_info` (
  `photo_id` int(10) unsigned NOT NULL,
  `file_name` varchar(100) NOT NULL,
  `file_size` int(10) unsigned NOT NULL DEFAULT '0',
  `mime_type` varchar(150) DEFAULT NULL,
  `extension` varchar(20) NOT NULL,
  `description` mediumtext,
  `width` smallint(4) unsigned NOT NULL DEFAULT '0',
  `height` smallint(4) unsigned NOT NULL DEFAULT '0',
  UNIQUE KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_link` (
  `link_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `is_custom` tinyint(1) NOT NULL DEFAULT '0',
  `link` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status_info` mediumtext,
  `privacy` tinyint(1) NOT NULL,
  `privacy_comment` tinyint(1) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `has_embed` tinyint(1) NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`link_id`),
  KEY `parent_user_id` (`parent_user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_music_song` (
  `song_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `album_id` mediumint(8) unsigned NOT NULL,
  `genre_id` smallint(4) unsigned NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `song_path` varchar(50) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `explicit` tinyint(1) NOT NULL,
  `duration` varchar(5) DEFAULT NULL,
  `ordering` tinyint(3) NOT NULL DEFAULT '0',
  `total_play` int(10) unsigned NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `total_score` decimal(4,2) NOT NULL DEFAULT '0.00',
  `total_rating` int(10) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`song_id`),
  KEY `user_id` (`user_id`),
  KEY `view_id_3` (`view_id`,`privacy`),
  KEY `view_id` (`view_id`,`privacy`,`genre_id`),
  KEY `view_id_2` (`view_id`,`privacy`,`is_featured`),
  KEY `view_id_4` (`view_id`,`privacy`,`user_id`),
  KEY `view_id_5` (`view_id`,`privacy`,`title`),
  KEY `view_id_6` (`view_id`,`privacy`,`module_id`,`item_id`),
  KEY `view_id_7` (`view_id`,`privacy`,`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_forum_post` (
  `post_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `thread_id` int(10) unsigned NOT NULL,
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `total_attachment` int(11) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `update_time` int(10) unsigned NOT NULL DEFAULT '0',
  `update_user` varchar(100) DEFAULT NULL,
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`post_id`),
  KEY `thread_id` (`thread_id`),
  KEY `user_id` (`user_id`),
  KEY `thread_id_2` (`thread_id`,`view_id`),
  KEY `view_id` (`view_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_feed` (
  `feed_id` int(10) unsigned NOT NULL,
  `photo_id` int(10) unsigned NOT NULL,
  KEY `feed_id` (`feed_id`),
  KEY `photo_id` (`photo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_photo_tag` (
  `tag_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `photo_id` int(10) unsigned NOT NULL,
  `user_id` int(11) unsigned NOT NULL,
  `tag_user_id` int(11) unsigned NOT NULL DEFAULT '0',
  `content` varchar(255) DEFAULT NULL,
  `time_stamp` int(10) unsigned NOT NULL,
  `position_x` smallint(4) unsigned NOT NULL DEFAULT '0',
  `position_y` smallint(4) unsigned NOT NULL DEFAULT '0',
  `width` smallint(4) unsigned NOT NULL DEFAULT '0',
  `height` smallint(4) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`tag_id`),
  KEY `photo_id` (`photo_id`),
  KEY `photo_id_2` (`photo_id`,`position_x`,`position_y`,`width`,`height`),
  KEY `photo_id_3` (`photo_id`,`tag_user_id`),
  KEY `photo_id_4` (`photo_id`,`user_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * /*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_actions` (
  `action_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `subject_id` int(11) unsigned NOT NULL,
  `object_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `object_id` int(11) unsigned NOT NULL,
  `body` text COLLATE utf8_unicode_ci,
  `params` text COLLATE utf8_unicode_ci,
  `date` datetime NOT NULL,
  `attachment_count` smallint(3) unsigned NOT NULL DEFAULT '0',
  `comment_count` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `like_count` mediumint(5) unsigned NOT NULL DEFAULT '0',
  `privacy` varchar(500) COLLATE utf8_unicode_ci DEFAULT NULL,
  `commentable` tinyint(1) NOT NULL DEFAULT '1',
  `shareable` tinyint(1) NOT NULL DEFAULT '1',
  `user_agent` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`action_id`),
  KEY `SUBJECT` (`subject_type`,`subject_id`),
  KEY `OBJECT` (`object_type`,`object_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_authorization_allow` (
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

/*
 * CREATE TABLE IF NOT EXISTS `engine4_activity_attachments` (
  `attachment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `action_id` int(11) unsigned NOT NULL,
  `type` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `id` int(11) unsigned NOT NULL,
  `mode` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`attachment_id`),
  KEY `action_id` (`action_id`),
  KEY `type_id` (`type`,`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
