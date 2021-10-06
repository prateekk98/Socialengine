<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    CoreContent.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_CoreContent extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_toTableTruncate = false;
  protected $_priority = 4500;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'page_text';
    $this->_toTable = 'engine4_core_content';
  }

  protected function _translateRow(array $data, $key = null)
  {
    //FIND THE PAGE DETAIL
    $fromPageModel = $this->getFromDb()->select()->from($this->getfromPrefix() . 'page', '*')
      ->where('page_id = ?', $data['page_id'])
      ->query()
      ->fetch();
    switch( $fromPageModel['title'] ) {
      case 'core.privacy_policy':
        //UPDATE THE PAGE CONTENT
        $data = array('name' => 'core_help_privacy', 'title' => "", 'description' => $data['text'], 'pageId' => 0);
        $this->_updatePageContent($data);
        break;
      case 'core.terms_use':
        //UPDATE THE PAGE CONTENT
        $data = array('name' => 'core_help_terms', 'title' => "", 'description' => $data['text'], 'pageId' => 0);
        $this->_updatePageContent($data);
        break;
      default :
        //INSERT THE PAGE CONTENT
        $this->_insertPageContent($data);
    }
    return false;
  }

  /*
   * find core.content widget and update it with core.html-block and insert the content on that.
   */
  protected function _updatePageContent($data)
  {
    $toDb = $this->getToDb();
    //FIND PAGE CONTENT ID
    $pageContent = $toDb->select()
      ->from($this->getToTable(), array('content_id'))
      ->join('engine4_core_pages', 'engine4_core_content.page_id=engine4_core_pages.page_id')
      ->where('engine4_core_content.name= ? ', 'core.content')
      ->where('engine4_core_pages.name= ? ', $data['name'])
      ->query()
      ->fetch();
    if( $pageContent ) {
      //UPDATE THE PAGE CONTENT 
      $toDb->update($this->getToTable(), array(
        'name' => 'core.html-block',
        'params' => Zend_Json::encode(array('title' => $data['title'], 'data' => $data['description']))
        ), array(
        'content_id = ?' => $pageContent['content_id']
      ));
    }
  }

  /*
   * INSERT THE PAGE CONTENT
   */
  protected function _insertPageContent($data)
  {
    //RETURN FALSE IF IT IS MOBILE PAGE CONTENT
    $module_id = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'page', 'module_id')
      ->where('page_id = ?', $data['page_id'])
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $module_id == 'mobile' )
      return;
    //FIND PAGE ID
    $pageId = $this->getPageMap($data['page_id']);
    //CHECKING CONTENT ALREADY EXIST
    $isContentAlreadyExist = $this->getToDb()->select()->from($this->getToTable(), 'page_id')
      ->where('page_id= ?', $pageId)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $isContentAlreadyExist )
      return;
    // Insert main content for page
    $this->getToDb()->insert($this->getToTable(), array(
      'page_id' => $pageId,
      'type' => 'container',
      'name' => 'main'
    ));
    $mainIdentity = $this->getToDb()->lastInsertId();

    // Insert main-middle content for page
    $this->getToDb()->insert($this->getToTable(), array(
      'page_id' => $pageId,
      'type' => 'container',
      'parent_content_id' => $mainIdentity,
      'name' => 'middle'
    ));
    $middleIdentity = $this->getToDb()->lastInsertId();

    // Insert html-block widget
    $this->getToDb()->insert($this->getToTable(), array(
      'page_id' => $pageId,
      'type' => 'widget',
      'parent_content_id' => $middleIdentity,
      'name' => 'core.html-block',
      'params' => Zend_Json::encode(array('title' => "", 'data' => $data['text']))
    ));
  }

}
