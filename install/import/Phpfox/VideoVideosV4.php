<?php

/**
 * Class Install_Import_Phpfox_VideoVideosV4
 */
class Install_Import_Phpfox_VideoVideosV4 extends Install_Import_Phpfox_Abstract
{
  protected $_toTable = 'engine4_video_videos';
  protected $_fromWhere = array('type_id=?' => 'PHPfox_Videos');
  protected $_priority = 300;

  private $_data = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'feed';
    $this->_toTable = 'engine4_video_videos';
    $this->_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');
  }

  protected function _translateRow(array $data, $key = null)
  {
    if( !isset($data['content']) ) {
      return false;
    }

    if( substr($data['content'], 0, 1) != '{' ) {
      return false;
    }

    $content = Zend_Json::decode($data['content']);

    if( !isset($content['embed_code']) ) {
      return false;
    }

    $videoId = false;
    $typeId = 1;
    $embed = $content['embed_code'];
    if( preg_match('/src=\"(.*?)\"/i', $embed, $matches) ) {
      $url = parse_url($matches[1]);

      if( strpos($url['host'], 'youtube') ) {
        $videoId = str_replace('/embed/', '', $url['path']);
      }
    }

    if( !$videoId ) {
      return false;
    }

    $newData = array();
    $newData['title'] = (empty($content['status_update']) ? $content['caption'] : $content['status_update']);
    $newData['code'] = $videoId;
    $newData['video_id'] = $data['video_id'];
    $newData['search'] = 1;
    $newData['owner_id'] = $data['user_id'];
    $newData['creation_date'] = $this->_translateTime($data['time_stamp']);
    $newData['modified_date'] = $this->_translateTime($data['time_stamp']);
    $newData['view_count'] = $data['total_view'];
    $newData['parent_id'] = $data['feed_id'];
    $newData['comment_count'] = 0;
    $newData['rating'] = 0;
    $newData['category_id'] = 0;
    $newData['status'] = 1;
    $newData['type'] = $typeId;

    if( isset($content['embed_image']) ) {
      try {
        $newData['photo_id'] = $this->_translatePhoto($content['embed_image'], array(
          'parent_type' => 'video',
          'parent_id' => $data['video_id'],
          'user_id' => $data['user_id'],
          'remoteServer' => true
        ));
      } catch( Exception $e ) {
        $this->_warning($e->getMessage(), 1);
      }
    }

    if( $newData['search'] ) {
      $this->_insertSearch('video', $newData['video_id'], $newData['title'], $newData['description']);
    }

    $this->_data = $newData;

    return $newData;
  }
}
