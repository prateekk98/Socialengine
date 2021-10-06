<?php

class Install_Import_Ning_BlogBlogs extends Install_Import_Ning_Abstract
{
  protected $_fromFile = 'ning-blogs-local.json';

  protected $_fromFileAlternate = 'ning-blogs.json';

  protected $_toTable = 'engine4_blog_blogs';

  protected $_resourceType = 'blog';

  protected $_priority = 700;

  protected function  _translateRow(array $data, $key = null)
  {
    $userIdentity = $this->getUserMap($data['contributorName']);
    $blogIdentity = $key + 1;

    $newData = array();

    $newData['blog_id'] = $blogIdentity;
    $newData['title'] = $data['title'] ? $data['title'] : 'Untitled';
    $newData['body'] = $data['description'];
    $newData['owner_type'] = 'user';
    $newData['owner_id'] = $userIdentity;
    $newData['creation_date'] = $this->_translateTime($data['createdDate']);
    $newData['modified_date'] = $this->_translateTime($data['updatedDate']);
    $newData['view_count'] = 0;
    $newData['comment_count'] = count((array) @$data['comments']);
    $newData['search'] = 1;
    $newData['draft'] = ( @$data['publishStatus'] != 'publish' );

    // privacy
    $this->_insertPrivacy($this->_resourceType, $newData['blog_id'], 'view');
    $this->_insertPrivacy($this->_resourceType, $newData['blog_id'], 'comment');
    // search
    $this->_insertSearch($this->_resourceType, $newData['blog_id'], array(
      'title' => $newData['title'],
      'description' => $newData['body']
    ));
    return $newData;
  }
}
