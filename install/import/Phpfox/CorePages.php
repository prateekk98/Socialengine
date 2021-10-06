<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    CorePages.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_CorePages extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_toTableTruncate = false;
  protected $_fromWhere = array('module_id<>?' => 'mobile');
  protected $_priority = 5000;

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'page';
    $this->_toTable = 'engine4_core_pages';
  }

  protected function _translateRow(array $data, $key = null)
  {
    //FIND PAGE CONTENT
    $pageContentModel = $this->getFromDb()->select()->from($this->getfromPrefix() . 'page_text', '*')
      ->where('page_id = ?', $data['page_id'])
      ->query()
      ->fetch();
    $pageContentModel['description'] = is_null($pageContentModel['description']) ? '' : $pageContentModel['description'];
    $pageContentModel['keyword'] = is_null($pageContentModel['keyword']) ? '' : $pageContentModel['keyword'];
    switch( $data['title'] ) {
      case 'core.privacy_policy':
        //UPDATE THE PRIVACY PAGE
        $data = array('name' => 'core_help_privacy', 'keyword' => $pageContentModel['keyword'], 'description' => $pageContentModel['description'], 'title' => $data['title_url']);
        $this->_updatePageMetaTags($data);
        break;
      case 'core.terms_use':
        //UPDATE THE TERM & CONDITION PAGE
        $data = array('name' => 'core_help_terms', 'keyword' => $pageContentModel['keyword'], 'description' => $pageContentModel['description'], 'title' => $data['title_url']);
        $this->_updatePageMetaTags($data);
        break;
      case 'core.about':
        $this->_insertAboutUs($data);
        break;
      default :
        //INSERT ALL OTHER PAGES
        //FIND ALL THE ALLOWED LEVEL WHO WILL BE ABLE TO SEE THIS PAGE
        $allowedLevels = $this->allowedAccessLevels($data['disallow_access']);
        $displayName = $data['title_url'];
        $name = str_replace('.', '_', $data['title']);
        //CHECKING THIS PAGE EXIST OR NOT INTO TARGET TABLE
        $pageIdentity = $this->getToDb()->select()->from($this->getToTable(), 'page_id')
            ->where('name = ?', $name)
            ->limit(1)
            ->query()->fetchColumn(0);
        //INSERT PAGE
        if( empty($pageIdentity) ) {
          $newData = array(
            'name' => $name,
            'custom' => 1,
            'displayname' => $displayName,
            'title' => $data['title_url'],
            'description' => $pageContentModel['description'],
            'keywords' => $pageContentModel['keyword'],
            'url' => null,
            'levels' => $allowedLevels
          );
          $this->getToDb()->insert($this->getToTable(), $newData);
        }
    }
    return false;
  }

  //UPDATE THE META TAG
  protected function _updatePageMetaTags($data)
  {
    $this->getToDb()->update($this->getToTable(), array(
      'keywords' => $data['keyword'],
      'description' => $data['description']
      ), array(
      'name = ?' => $data['name']
    ));
  }

  /*
   * INSERT ABOUT US PAGE
   */
  protected function _insertAboutUs($data)
  {
    //FIND WHEATER PAGE IS ALREADY INSERTED OR NOT 
    $pageIdentity = $this->getToDb()->select()->from($this->getToTable(), 'page_id')
        ->where('title = ?', $data['title_url'])
        ->limit(1)
        ->query()->fetchColumn(0);

    if( !empty($pageIdentity) )
      return;
    $name = str_replace('.', '_', $data['title']);
    //ALLOWED LEVELS
    $allowedLevels = $this->allowedAccessLevels($data['disallow_access']);

    $menuName = "";
    $displayName = $data['title_url'];
    $param = array("uri" => "", "icon" => "", "target" => "", "enabled" => 1);
    $path = $this->getApplicationDirUrl();
    $param['uri'] = $path . DIRECTORY_SEPARATOR . 'pages/about-us';
    //PREPARING AN ARRAY FOR MENU ITEM
    $menuItemArr = array(
      "name" => 'custom',
      "module" => 'core',
      "label" => 'About us',
      "params" => Zend_Json_Encoder::encode($param),
      "menu" => 'core_footer',
      "submenu" => '',
      "enabled" => 1,
      "custom" => 1,
      "order" => 999
    );
    $newData = array(
      'name' => $name,
      'custom' => 1,
      'displayname' => $displayName,
      'title' => $data['title_url'],
      'description' => '',
      'keywords' => '',
      'url' => 'about-us',
      'levels' => $allowedLevels
    );
    //INSERT INTO PAGES
    $this->getToDb()->insert($this->getToTable(), $newData);
    $menuId = $this->getToDb()->select()
      ->from('engine4_core_menuitems', 'id')
      ->where('label = ?', 'About us')
      ->where('menu = ?', 'core_footer')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $menuId === false ) {
      //INSERT THE MENU ITEM
      $this->getToDb()->insert('engine4_core_menuitems', $menuItemArr);
      $mainIdentity = $this->getToDb()->lastInsertId();
      if( $menuName == "" )
        $menuName = "custom_" . $mainIdentity;
      //UPDATE THE MENU ITEM
      $this->getToDb()->update('engine4_core_menuitems', array(
        "name" => $menuName
        ), array('id = ?' => $mainIdentity));
    } else {
      //UPDATE THE MENU ITEMS
      $this->getToDb()->update('engine4_core_menuitems', array(
        'params' => Zend_Json_Encoder::encode($param)
        ), array(
        'id = ?' => $menuId
      ));
    }
  }

  /*
   * FIND ALLOWED ACCESS LEVEL
   */
  protected function allowedAccessLevels($disAllowedAccess)
  {
    $allowedLevels = null;
    if( is_null($disAllowedAccess) || strlen($disAllowedAccess) == 0 ) {
      $levels = $this->getToDb()->select()
        ->from('engine4_authorization_levels', 'level_id')
        ->query()
        ->fetchAll();
    } else {
      $disallowedLevels = unserialize($disAllowedAccess);
      $bannedUserGroupId = $this->getParam('bannedUserGroupId');
      $adminUserGroupId = $this->getParam('adminUserGroupId');
      $registeredUserGroupId = $this->getParam('registeredUserGroupId');
      $guestUserGroupId = $this->getParam('guestUserGroupId');
      $staffUserGroupId = $this->getParam('staffUserGroupId');
      $level = array();
      foreach( $disallowedLevels as $dAllow ) {
        switch( $dAllow ) {
          case $adminUserGroupId :
            $level[] = $this->getLevelId('admin', '');
            break;
          case $registeredUserGroupId :
            $level[] = $this->getLevelId('user', 'default');
            break;
          case $guestUserGroupId :
            $level[] = $this->getLevelId('public', 'public');
            break;
          case $staffUserGroupId :
            $lvl = $this->getLevelId('moderator', '');
            if( $lvl === false || is_null($lvl) || empty($lvl) )
              $level[] = $this->getLevelId('admin', '');
            else
              $level[] = $lvl;
            break;
          case $bannedUserGroupId :
            $level[] = $this->getLevelId('user', 'default');
            break;
          default :
            $disAllowedLevels[] = $dAllow;
        }
      }
      $disAllowedGrpNames = array();
      if( count($disAllowedLevels) > 0 ) {
        $disAllowedGrpNames = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'user_group', 'title')
          ->where('user_group_id in ( ? ) ', $disAllowedLevels)
          ->query()
          ->fetchAll();
      }
      foreach( $disAllowedGrpNames as $grpName ) {

        switch( $grpName['title'] ) {
          case 'Administrator':
            $level[] = $this->getLevelId('admin', '');
            break;
          case 'Registered User':
            $level[] = $this->getLevelId('user', 'default');
            break;
          case 'Guest':
            $level[] = $this->getLevelId('public', 'public');
            break;
          case 'Staff':
            $lvl = $this->getLevelId('moderator', '');
            if( $lvl === false || is_null($lvl) || empty($lvl) )
              $level[] = $this->getLevelId('admin', '');
            else
              $level[] = $lvl;
            break;
          case 'Banned':
            $level[] = $this->getLevelId('user', 'default');
            break;
          default :
            $lvl = $this->getLevelIdByTitle($grpName['title']);
            if( $lvl === false || is_null($lvl) || empty($lvl) )
              $level[] = $this->getLevelId('user', 'default');
            else
              $level[] = $lvl;
        }
      }
      $levels = $this->getToDb()->select()
        ->from('engine4_authorization_levels', 'level_id')
        ->where('level_id not in( ? )', $level)
        ->query()
        ->fetchAll();
    }
    foreach( $levels as $level )
      $al[] = $level['level_id'];
    if( count($al) > 0 )
      $allowedLevels = json_encode($al);

    return $allowedLevels;
  }
}
