<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    MusicPlaylists.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_MusicPlaylists extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('module_id is NULL' => NULL);
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_truncateTable($this->getToDb(), 'engine4_music_playlist_songs');
    $this->_fromTable = $this->getFromPrefix() . 'music_album';
    $this->_toTable = 'engine4_music_playlists';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {

    //GET TEXT
    $text = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'music_album_text', 'text')
      ->where('album_id = ?', $data['album_id'])
      ->query()
      ->fetchColumn();

    //MAKING EVENT ARRAY FOR INSERTION
    $newData = array();
    $newData['playlist_id'] = $data['album_id'];
    $newData['title'] = $data['name'];
    $newData['description'] = $text ? $text : '';
    $newData['owner_type'] = 'user';
    $newData['search'] = 1;
    $newData['owner_id'] = $data['user_id'];
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['comment_count'] = $data['total_comment'];
    $newData['play_count'] = $data['total_play'];

    //SET IMAGE AND INSERT THE MAIN PHOTO OF PLAYLIST
    if( $data['image_path'] ) {
      $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic/music' . DIRECTORY_SEPARATOR . $data['image_path']);
      $file = $des[0];
      if( isset($des[1]) )
        $file = $des[0] . $des[1];
      if( $file ) {
        try {
          //CREATE FILE FOR THE PHOTO
          if( $this->getParam('resizePhotos', true) ) {
            $file_id = $this->_translatePhoto($file, array(
              'parent_type' => 'music_playlist',
              'parent_id' => $data['album_id'],
              'user_id' => $data['user_id'],
            ));
          } else {
            $file_id = $this->_translateFile($file, array(
              'parent_type' => 'music_playlist',
              'parent_id' => $data['album_id'],
              'user_id' => $data['user_id'],
              ), true);
          }
        } catch( Exception $e ) {
          $file_id = null;
          $this->_logFile($e->getMessage());
        }

        $newData['photo_id'] = $file_id;
      }
    }
    //INSERT THE PLAYLIST
    $this->getToDb()->insert('engine4_music_playlists', $newData);

    //GET PLAYLIST ID
    $playlist_id = $this->getToDb()->lastInsertId();

    //GET SONGS INFORMATION
    $songsInfo = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'music_song', '*')
      ->where('album_id = ?', $data['album_id'])
      ->query()
      ->fetchAll();
    $songsData = array();
    //INSERT ALL THE SONGS OF THIS PLAYLIST
    foreach( $songsInfo as $songs ) {
      $songsData['song_id'] = $songs['song_id'];
      $songsData['playlist_id'] = $playlist_id;
      $songsData['title'] = $songs['title'];
      $songsData['play_count'] = $songs['total_play'];
      $songsData['order'] = $songs['ordering'];
      if( $songs['song_path'] ) {
        $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/music' . DIRECTORY_SEPARATOR . $songs['song_path']);
        $file = $des[0];
        if( isset($des[1]) )
          $file = $des[0] . $des[1];
        if( $file ) {
          try {
            //CREATE THE MUSIC FILE
            $file_id = $this->_translateFile($file, array(
              'parent_type' => 'music_playlist_song',
              'parent_id' => $playlist_id,
              // 'user_id' => $songs['user_id'],
              ), true);
          } catch( Exception $e ) {
            $file_id = null;
            $this->_warning($e->getMessage(), 1);
          }

          $songsData['file_id'] = $file_id;
        }
      }
      //INSERT THE MUSIC
      $this->getToDb()->insert('engine4_music_playlist_songs', $songsData);
      //INSERT SEARCH
      $this->_insertSearch('music_playlist_song', $songs['song_id'], $songsData['title']);
    }

    // See if there is an existing playlist for this user
    $playlistIdentity = $this->getToDb()->select()
      ->from('engine4_music_playlists', 'playlist_id')
      ->where('owner_type = ?', 'user')
      ->where('owner_id = ?', $data['user_id'])
      ->where('special = ?', 'wall')
      ->limit(1)
      ->query()
      ->fetchColumn(0)
    ;

    // No playlist, make new one
    if( !$playlistIdentity ) {
      $maxId = $this->getToDb()
        ->select()
        ->from('engine4_music_playlists', 'max(playlist_id)')
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $maxId === false || $maxId < 1000000 )
        $maxId = 1000000;
      else
        $maxId++;
      //INSERT THE MUSIC PLAYLIST
      $this->getToDb()->insert('engine4_music_playlists', array(
        'playlist_id' => $maxId,
        'title' => 'Profile Playlist',
        'description' => '',
        'owner_type' => 'user',
        'owner_id' => $data['user_id'],
        'special' => 'wall',
        'search' => 1,
        'profile' => 1,
        'creation_date' => $this->_translateTime(time()),
        'modified_date' => $this->_translateTime(time()),
      ));
      $playlistIdentity = $this->getToDb()->lastInsertId();
      //SEARCH
      if( @$newData['search'] ) {
        $this->_insertSearch('music_playlist', $playlistIdentity, 'Profile Playlist');
      }
    }

    if( $playlistIdentity ) {

      //GET SONGS INFORMATION
      $songsInfo = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'music_song', '*')
        ->where('album_id = ?', 0)
        ->where('user_id = ?', $data['user_id'])
        ->query()
        ->fetchAll();
      $songsData = array();
      //INSERT THE ALL THE SONGS OF PLAYLIST
      foreach( $songsInfo as $songs ) {

        $playlistSongId = $this->getToDb()->select()
          ->from('engine4_music_playlist_songs', 'song_id')
          ->where('playlist_id =? ', $playlistIdentity)
          ->where('song_id =? ', $songs['song_id'])
          ->limit(1)
          ->query()
          ->fetchColumn(0);
        if( !$playlistSongId ) {
          $songsData['playlist_id'] = $playlistIdentity;
          $songsData['song_id'] = $songs['song_id'];
          $songsData['title'] = $songs['title'];
          if( $songs['song_path'] ) {
            $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/music' . DIRECTORY_SEPARATOR . $songs['song_path']);
            $file = $des[0];
            if( isset($des[1]) )
              $file = $des[0] . $des[1];

            if( $file ) {
              try {
                //CREATE THE FILE OF SONG
                $file_id = $this->_translateFile($file, array(
                  'parent_type' => 'music_playlist_song',
                  'parent_id' => $playlistIdentity,
                  ), true);
              } catch( Exception $e ) {
                $file_id = null;
                $this->_warning($e->getMessage(), 1);
              }

              $songsData['file_id'] = $file_id;
            }
          }
          $this->getToDb()->insert('engine4_music_playlist_songs', $songsData);
          //SEARCH
          $this->_insertSearch('music_playlist_song', $songs['song_id'], $songsData['title']);
        }
      }
      try {
        $this->getToDb()->query("DELETE FROM `engine4_authorization_allow` WHERE `engine4_authorization_allow`.`resource_type` = 'music_playlist' AND `engine4_authorization_allow`.`resource_id` = $playlistIdentity");
        //DELETE PRIVACY IF ALREADY EXIST
        $this->getToDb()->delete('engine4_authorization_allow', array(
          'resource_type' => 'music_playlist',
          'resource_id' => $playlistIdentity
        ));
        //INSERT PRIVACY
        $this->_insertPrivacy('music_playlist', $playlistIdentity, 'view', $this->_translateMusicPrivacy(0));
        $this->_insertPrivacy('music_playlist', $playlistIdentity, 'comment', $this->_translateMusicPrivacy(0));
      } catch( Exception $e ) {
        $this->_error('Problem adding privacy options for object id ' . $playlistIdentity . ' : ' . $e->getMessage());
      }
    }

    //PRIVACY
    try {
      //set privacy
      $musicPrivacy = $this->_translateMusicPrivacy($data['privacy']);
      $newData['view_privacy'] = $musicPrivacy[0];

      $this->_insertPrivacy('music_playlist', $playlist_id, 'view', $this->_translateMusicPrivacy($data['privacy']));
      $this->_insertPrivacy('music_playlist', $playlist_id, 'comment', $this->_translateMusicPrivacy($data['privacy_comment']));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $playlist_id . ' : ' . $e->getMessage());
    }

    //SEARCH
    if( @$newData['search'] ) {
      $this->_insertSearch('music_playlist', $playlist_id, @$newData['title']);
    }
  }
}

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_music_album` (
  `album_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `view_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `user_id` int(10) unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `year` char(4) DEFAULT NULL,
  `image_path` varchar(75) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `total_track` smallint(4) unsigned NOT NULL DEFAULT '0',
  `total_play` int(10) unsigned NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `total_score` decimal(4,2) NOT NULL DEFAULT '0.00',
  `total_rating` int(10) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL,
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`album_id`),
  KEY `view_id` (`view_id`,`privacy`),
  KEY `view_id_2` (`view_id`,`privacy`,`is_featured`),
  KEY `user_id` (`user_id`),
  KEY `view_id_3` (`view_id`,`privacy`,`total_track`,`module_id`,`item_id`),
  KEY `view_id_4` (`view_id`,`privacy`,`total_track`,`item_id`),
  KEY `view_id_5` (`view_id`,`user_id`,`item_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_music_album_text` (
  `album_id` int(10) unsigned NOT NULL,
  `text` mediumtext,
  `text_parsed` mediumtext,
  UNIQUE KEY `album_id` (`album_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
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
 * CREATE TABLE IF NOT EXISTS `engine4_music_playlists` (
  `playlist_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(63) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `photo_id` int(11) unsigned NOT NULL DEFAULT '0',
  `owner_type` varchar(24) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) unsigned NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `profile` tinyint(1) NOT NULL DEFAULT '0',
  `special` enum('wall','message') COLLATE utf8_unicode_ci DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `play_count` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`playlist_id`),
  KEY `creation_date` (`creation_date`),
  KEY `play_count` (`play_count`),
  KEY `owner_id` (`owner_type`,`owner_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_music_playlist_songs` (
  `song_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `playlist_id` int(11) unsigned NOT NULL,
  `file_id` int(11) unsigned NOT NULL,
  `title` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `play_count` int(11) unsigned NOT NULL DEFAULT '0',
  `order` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`song_id`),
  KEY `playlist_id` (`playlist_id`,`file_id`),
  KEY `play_count` (`play_count`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1 ;
 */
