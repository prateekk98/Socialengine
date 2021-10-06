<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    VideoVideos.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_VideoVideos extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_fromWhere = array('in_process=?' => 0, 'module_id<>?' => 'pages');
  protected $_fromGroupBy = array();
  protected $_warningMessage = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'video';
    $this->_toTable = 'engine4_video_videos';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
    $this->_fromGroupBy = array('video_id');
  }
  
  public function handleIframelyInformation($uri) {

    //$iframelyDisallowHost = Engine_Api::_()->getApi('settings', 'core')->getSetting('video_iframely_disallow');
    if (parse_url($uri, PHP_URL_SCHEME) === null) {
        $uri = "http://" . $uri;
    }
    $uriHost = Zend_Uri::factory($uri)->getHost();
//     if ($iframelyDisallowHost && in_array($uriHost, $iframelyDisallowHost)) {
//         return;
//     }
    if(in_array($uriHost, array('youtube.com','www.youtube.com','youtube', 'youtu.be'))){
        return $this->YoutubeVideoInfo($uri);
    } else {
        $config = Engine_Api::_()->getApi('settings', 'core')->core_iframely;
        $iframely = Engine_Iframely::factory($config)->get($uri);
    }
    if (!in_array('player', array_keys($iframely['links']))) {
        return;
    }
    $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
    if (!empty($iframely['links']['thumbnail'])) {
        $information['thumbnail'] = $iframely['links']['thumbnail'][0]['href'];
        if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
            $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
            $information['thumbnail'] = "http://" . $information['thumbnail'];
        }
    }
    if (!empty($iframely['meta']['title'])) {
        $information['title'] = $iframely['meta']['title'];
    }
    if (!empty($iframely['meta']['description'])) {
        $information['description'] = $iframely['meta']['description'];
    }
    if (!empty($iframely['meta']['duration'])) {
        $information['duration'] = $iframely['meta']['duration'];
    }
    $information['code'] = $iframely['html'];
    return $information;
  }
  
  public function getYoutubeIdFromUrl($url) {
    $parts = parse_url($url);
    if(isset($parts['query'])) {
      parse_str($parts['query'], $qs);
      if(isset($qs['v'])){
        return $qs['v'];
      } else if(isset($qs['vi'])){
        return $qs['vi'];
      }
    }
    if(isset($parts['path'])){
      $path = explode('/', trim($parts['path'], '/'));
      return $path[count($path)-1];
    }
    return false;
  }

  public function YoutubeVideoInfo($uri) {
  
    $adapter = Zend_Registry::get('Zend_Db');
    $coresettingsTable = new Zend_Db_Table(array(
      'db' => $adapter,
      'name' => 'engine4_core_settings',
    ));
    
    $video_id = $this->getYoutubeIdFromUrl($uri);
    $key = $coresettingsTable->select()
                              ->from($coresettingsTable, 'value')
                              ->where('name = ?', 'video.youtube.apikey')
                              ->query()
                              ->fetchColumn();
    if(empty($key)){
        return;
    }
    $url = 'https://www.googleapis.com/youtube/v3/videos?id='.$video_id.'&key='.$key.'&part=snippet,player,contentDetails';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_REFERER, $_SERVER['HTTP_REFERER']);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PROXYPORT, 3128);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $response = curl_exec($ch);
    curl_close($ch);
    $response_a = json_decode($response,TRUE);    
    $iframely =  $response_a['items'][0];
    if (!in_array('player', array_keys($iframely))) {
        return;
    }
    $information = array('thumbnail' => '', 'title' => '', 'description' => '', 'duration' => '');
    if (!empty($iframely['snippet']['thumbnails'])) {
        $information['thumbnail'] = $iframely['snippet']['thumbnails']['high']['url'];
        if (parse_url($information['thumbnail'], PHP_URL_SCHEME) === null) {
            $information['thumbnail'] = str_replace(array('://', '//'), '', $information['thumbnail']);
            $information['thumbnail'] = "http://" . $information['thumbnail'];
        }
    }
    if (!empty($iframely['snippet']['title'])) {
        $information['title'] = $iframely['snippet']['title'];
    }
    if (!empty($iframely['snippet']['description'])) {
        $information['description'] = $iframely['snippet']['description'];
    }
    if (!empty($iframely['contentDetails']['duration'])) {
        $information['duration'] =  Engine_Date::convertISO8601IntoSeconds($iframely['contentDetails']['duration']);
    }
    $information['code'] = $iframely['player']['embedHtml'];
    return $information; 
  }

  protected function _translateRow(array $data, $key = null)
  {

    $newData = array();

    //GET VIDEO URL
    $video_url = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'video_embed', 'video_url')
      ->where('video_id = ?', $data['video_id'])
      ->query()
      ->fetchColumn();

    //GET RATING
//     $rating = $this->getFromDb()->select()
//       ->from($this->getfromPrefix() . 'video_rating', 'rating')
//       ->where('item_id = ?', $data['video_id'])
//       ->query()
//       ->fetchColumn();

    //GET CATEGORY ID
    $category_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'video_category_data', 'category_id')
      ->where('video_id = ?', $data['video_id'])
      ->query()
      ->fetchColumn();

    //GET VIDEO DESCRIITION
    $description = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'video_text', 'text')
      ->where('video_id = ?', $data['video_id'])
      ->query()
      ->fetchColumn();

    //MAKING VIDEO ARRAY FOR INSERTION
    $newData['video_id'] = $data['video_id'];
    $newData['search'] = 1;
    $newData['owner_id'] = $data['user_id'];
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['view_count'] = $data['total_view'];
    $newData['comment_count'] = $data['total_comment'];
    //$newData['rating'] = $rating ? (int) ($rating / 2) : 0;
    $newData['category_id'] = $category_id;
    $newData['status'] = 1;

    //CHECKING SOURCE OF VIDEO
    if( $video_url && (strpos($video_url, 'vimeo') || (strpos($video_url, 'youtube') || strpos($video_url, 'youtu.be'))) && $data['is_stream'] == 1 ) {

      //SELECTION OF VIDEO DETAILS
      if( strpos($video_url, 'vimeo') ) {
        $pathInfo = @pathinfo($video_url);
        $video_code = $pathInfo['basename'];
        $newData['code'] = $video_code;
        $newData['type'] = 2;
        $vimeodata = simplexml_load_file("http://vimeo.com/api/v2/video/" . $video_code . ".xml");
        $title = $vimeodata->video->title;
        $newData['title'] = is_null($title) ? '' : $title;
        $newData['description'] = $vimeodata->video->description;
        $newData['duration'] = $vimeodata->video->duration;
      } elseif( strpos($video_url, 'youtube') || strpos($video_url, 'youtu.be') ) {

        $information = $this->handleIframelyInformation($video_url);
        
        $newData['title'] = $information['title'];
        $newData['description'] = $information['description'];
        $newData['code'] = $information['code'];
        //$newData['thumbnail'] = $information['thumbnail'];
        $newData['duration'] = $information['duration'];
        $newData['type'] = 'iframely';

//         $new_code = @pathinfo($video_url);
//         $youtube = "http://www.youtube.com/oembed?url=" . $video_url;
//         $ydata = @file_get_contents($youtube);
//         $decoded_data = json_decode($ydata);
//         $newData['title'] = (is_null($decoded_data) || is_null($decoded_data->title)) ? '' : ($decoded_data->title);
//         $url = preg_replace("/#!/", "?", $video_url);
//         $newData['type'] = 1;
//         // get v variable from the url
//         $arr = array();
//         $arr = @parse_url($url);
//         if( $arr['host'] === 'youtu.be' ) {
//           $ydata = explode("?", $new_code['basename']);
//           $code = $ydata[0];
//           $newData['code'] = $code;
//         } else {
//           $parameters = $arr["query"];
//           parse_str($parameters, $decoded_data);
// 
//           $code = $decoded_data['v'];
//           if( $code == "" ) {
//             $code = $new_code['basename'];
//           }
//           $newData['code'] = $code;
//         }
      }
    } else if( $data['is_stream'] == 0 ) {
      $newData['type'] = 3;
      $newData['code'] = '';
      $newData['title'] = is_null($data['title']) ? '' : $data['title'];
      $newData['description'] = $description ? $description : '';
      //list($minutes, $seconds) = preg_split('[:]', $data['duration']);
      $minutes = 0;
      $seconds = 0;
      $dArr = explode(':', $data['duration']);
      if( count($dArr) >= 2 ) {
        $minutes = $dArr[0];
        $seconds = $dArr[1];
      } else if( count($dArr) == 1 ) {
        $minutes = $dArr[0];
        $seconds = 0;
      }
      $duration = ceil($seconds + ($minutes * 60));
      $newData['duration'] = $duration;
      if( $data['destination'] ) {
        $file = $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/video' . DIRECTORY_SEPARATOR . $data['destination'];
        //INSERTION OF VIDEO
        try {
          $file_id = $this->_translateFile($file, array(
            'parent_type' => 'video',
            'parent_id' => $data['video_id'],
            'user_id' => $data['user_id'],
            ), true);
        } catch( Exception $e ) {
          $file_id = null;
          $this->_warning($e->getMessage(), 1);
        }
        $newData['file_id'] = $file_id;
      }
    }
    $newData['duration'] = (!isset($newData['duration']) || is_null($newData['duration'])) ? '' : $newData['duration'];
    $newData['description'] = (!isset($newData['description']) || is_null($newData['description'])) ? '' : $newData['description'];
    //CHECKING FOR PHOTO OF THIS VIDEO EXIST AND IF EXIST THEN INSERTION OF THAT PHOTO.
    
    if( $data['image_path'] ) { 
      $des = explode('%s', $this->getFromPath() . DIRECTORY_SEPARATOR . 'file/pic' . DIRECTORY_SEPARATOR . $data['image_path']);
      
      //Image size change 120 to 500
      $file = $des[0] . '_500' . $des[1];
      if( $file ) {
        try {
          if( $this->getParam('resizePhotos', true) ) {
            $file_id = $this->_translatePhoto($file, array(
              'parent_type' => 'video',
              'parent_id' => $data['video_id'],
              'user_id' => $data['user_id'],
            ));
          } else {
            $file_id = $this->_translateFile($file, array(
              'parent_type' => 'video',
              'parent_id' => $data['video_id'],
              'user_id' => $data['user_id'],
              ), true);
          }
        } catch( Exception $e ) {
          $file_id = null;
          $this->_warning($e->getMessage(), 1);
        }

        $newData['photo_id'] = $file_id;
      }
    }

    //PRIVACY
    try {
      //set privacy
      $videoPrivacy = $this->_translateVideoPrivacy($data['privacy']);
      $newData['view_privacy'] = $videoPrivacy[0];

      $this->_insertPrivacy('video', $data['video_id'], 'view', $this->_translateVideoPrivacy($data['privacy']));
      $this->_insertPrivacy('video', $data['video_id'], 'comment', $this->_translateVideoPrivacy($data['privacy_comment']));
    } catch( Exception $e ) {
      $this->_error('Problem adding privacy options for object id ' . $data['video_id'] . ' : ' . $e->getMessage());
    }

    //SEARCH
    if( @$newData['search'] ) {
      $this->_insertSearch('video', @$newData['video_id'], @$newData['title'], @$newData['description']);
    }

    return $newData;
  }
}

/*
CREATE TABLE IF NOT EXISTS `phpfox_video` (
  `video_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `in_process` tinyint(1) NOT NULL DEFAULT '0',
  `is_stream` tinyint(1) NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_spotlight` tinyint(1) NOT NULL DEFAULT '0',
  `is_sponsor` tinyint(1) NOT NULL DEFAULT '0',
  `view_id` tinyint(1) NOT NULL DEFAULT '0',
  `module_id` varchar(75) DEFAULT NULL,
  `item_id` int(10) unsigned NOT NULL DEFAULT '0',
  `privacy` tinyint(1) NOT NULL DEFAULT '0',
  `privacy_comment` tinyint(1) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `parent_user_id` int(10) unsigned NOT NULL DEFAULT '0',
  `destination` varchar(75) DEFAULT NULL,
  `server_id` tinyint(1) NOT NULL DEFAULT '0',
  `file_ext` varchar(10) DEFAULT NULL,
  `duration` varchar(8) DEFAULT NULL,
  `resolution_x` varchar(4) DEFAULT NULL,
  `resolution_y` varchar(4) DEFAULT NULL,
  `image_path` varchar(75) DEFAULT NULL,
  `image_server_id` tinyint(1) NOT NULL DEFAULT '0',
  `total_comment` int(10) unsigned NOT NULL DEFAULT '0',
  `total_like` int(10) unsigned NOT NULL DEFAULT '0',
  `total_dislike` int(10) unsigned NOT NULL DEFAULT '0',
  `total_score` decimal(4,2) NOT NULL DEFAULT '0.00',
  `total_rating` int(10) unsigned NOT NULL DEFAULT '0',
  `time_stamp` int(10) unsigned NOT NULL DEFAULT '0',
  `total_view` int(10) unsigned NOT NULL DEFAULT '0',
  `is_viewed` tinyint(1) NOT NULL DEFAULT '0',
  `custom_v_id` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`video_id`),
  KEY `in_process` (`in_process`),
  KEY `user_id` (`user_id`),
  KEY `view_id` (`view_id`),
  KEY `in_process_2` (`in_process`,`view_id`,`item_id`,`privacy`),
  KEY `in_process_3` (`in_process`,`view_id`,`item_id`,`user_id`),
  KEY `in_process_4` (`in_process`,`view_id`,`item_id`,`privacy`,`title`),
  KEY `in_process_5` (`in_process`,`view_id`,`item_id`,`privacy`,`user_id`),
  KEY `in_process_6` (`in_process`,`view_id`,`privacy`,`title`),
  KEY `custom_v_id` (`custom_v_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */


/*
CREATE TABLE IF NOT EXISTS `phpfox_video_category` (
  `category_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `name` varchar(255) NOT NULL,
  `time_stamp` int(10) unsigned NOT NULL DEFAULT '0',
  `used` int(10) unsigned NOT NULL DEFAULT '0',
  `ordering` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`category_id`),
  KEY `parent_id` (`parent_id`,`is_active`),
  KEY `is_active` (`is_active`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
 */

/*
 * CREATE TABLE IF NOT EXISTS `phpfox_video_category_data` (
  `video_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  KEY `category_id` (`category_id`),
  KEY `video_id` (`video_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
 */

/*
 * CREATE TABLE IF NOT EXISTS `engine4_video_videos` (
  `video_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `description` text COLLATE utf8_unicode_ci NOT NULL,
  `search` tinyint(1) NOT NULL DEFAULT '1',
  `owner_type` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `owner_id` int(11) NOT NULL,
  `parent_type` varchar(128) CHARACTER SET latin1 COLLATE latin1_general_ci DEFAULT NULL,
  `parent_id` int(11) unsigned DEFAULT NULL,
  `creation_date` datetime NOT NULL,
  `modified_date` datetime NOT NULL,
  `view_count` int(11) unsigned NOT NULL DEFAULT '0',
  `comment_count` int(11) unsigned NOT NULL DEFAULT '0',
  `type` tinyint(1) NOT NULL,
  `code` varchar(150) COLLATE utf8_unicode_ci NOT NULL,
  `photo_id` int(11) unsigned DEFAULT NULL,
  `rating` float NOT NULL,
  `category_id` int(11) unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL,
  `file_id` varchar(11) COLLATE utf8_unicode_ci NOT NULL,
  `duration` int(9) unsigned NOT NULL,
  `rotation` smallint(5) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`video_id`),
  KEY `owner_id` (`owner_id`,`owner_type`),
  KEY `search` (`search`),
  KEY `creation_date` (`creation_date`),
  KEY `view_count` (`view_count`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci AUTO_INCREMENT=1;
 */
