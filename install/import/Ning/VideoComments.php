<?php

class Install_Import_Ning_VideoComments extends Install_Import_Ning_Abstract
{
  protected $_fromFile = 'ning-videos-local.json';

  protected $_fromFileAlternate = 'ning-videos.json';

  protected $_toTable = 'engine4_core_comments';

  protected $_resourceType = 'video';

  public function reset()
  {
    $this->_deleteComments();
  }

  protected function  _translateRow(array $data, $key = null)
  {
    if( !isset($data['comments']) || !is_array($data['comments']) || count($data['comments']) < 1 ) {
      return false;
    }

    $videoIdentity = $key + 1;

    foreach( array_reverse($data['comments']) as $commentKey => $commentData ) {
      $commentUserIdentity = $this->getUserMap($commentData['contributorName']);

      // Insert into comments?
      $this->getToDb()->insert($this->getToTable(), array(
        'resource_type' => $this->_resourceType,
        'resource_id' => $videoIdentity,
        'poster_type' => 'user',
        'poster_id' => $commentUserIdentity,
        'body' => $this->_translateCommentBody($commentData['description']),
        'creation_date' => $this->_translateTime($commentData['createdDate']),
      ));
    }

    return false;
  }
}
