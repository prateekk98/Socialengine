<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Abstract.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_Abstract extends Install_Import_PhpfoxDbAbstract
{

  static protected $_commentMap;

  /*
   * This function returns the pharse label by taking the pharse variable.
   */
  public function getLabelByPharseVar($pharseVar)
  {
    if( empty($pharseVar) || is_null($pharseVar) )
      return '';
    //pharse var along with their module id so spliting module id and pharse var
    $pharseArr = explode('.', $pharseVar);
    if( count($pharseArr) != 2 )
      return '';
    // Selecting the pharse label
    $label = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'language_phrase', 'text')
      ->where('module_id = ?', $pharseArr[0])
      ->where('var_name = ?', $pharseArr[1])
      ->where('language_id = ?', 'en')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( is_null($label) || empty($label) )
      return '';

    return $label;
  }

  public function getPharseLabel($pharse)
  {
    $pharse = str_ireplace("{phrase var=&#039;", "", $pharse, $count);
    //if nothing replaced then send it means this is not a phrase.
    if( $count == 0 )
      return $pharse;
    $pharse = str_ireplace("&#039;}", "", $pharse, $count);
    return mb_convert_encoding($this->getLabelByPharseVar($pharse), "UTF-8", "HTML-ENTITIES");
  }

  /*
   * INSERT THE PRIVACY
   */
  protected function _insertPrivacy($resourceType, $resourceId, $action, $roles)
  {
    if( is_string($roles) ) {
      $roles = array($roles);
    } else if( is_array($roles) ) {
      $roles = array_filter($roles, 'is_string');
    }
    if( !is_array($roles) || empty($roles) ) {
      return;
    }

    $newData = array();
    //INSERTION OF AUTHORIZATION ALLOW FOR EACH ROLE
    foreach( $roles as $key => $role ) {
      $newData = array(
        'resource_type' => $resourceType,
        'resource_id' => $resourceId,
        'action' => $action,
        'role' => $role,
        'value' => 1,
      );
      if( $role == 'group_list' ) {
        $newData = array_merge($newData, array('role_id' => $key));
      }

      try {
        $this->getToDb()->insert('engine4_authorization_allow', $newData);
      } catch( Exception $e ) {
        $this->_error('Problem adding privacy options for object id ' . $resourceId . ' : ' . $e->getMessage());
      }
    }
  }

  /*
   * INSERT CORE SEARCH
   */
  protected function _insertSearch($type, $id, $title, $description = null, $keywords = null)
  {
    $title = trim(strip_tags((string) $title));
    $description = trim(strip_tags((string) $description));
    $keywords = trim(strip_tags((string) $keywords));

    if( empty($type) || empty($id) || ('' == $title && '' == $description && '' == $keywords) ) {
      return;
    }

    try {
      $this->getToDb()->insert('engine4_core_search', array(
        'type' => (string) $type,
        'id' => (integer) $id,
        'title' => (string) $title,
        'description' => (string) $description,
        'keywords' => (string) $keywords,
      ));
    } catch( Exception $e ) {
      $this->_log($e, Zend_Log::WARN);
    }
  }

  /*
   * RETURN THE BLOG PRIVACY
   */
  protected function _translateBlogPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE BLOG PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    return $arr;
  }

  /*
   * RETURN THE SOCIAL ENGINE POLL PRIVACY VALUE
   */
  protected function _translatePollPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE POLL PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    return $arr;
  }

  /*
   *  RETURN THE SOCIAL ENGINE MUSIC PRIVACY VALUE
   */
  protected function _translateMusicPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE MUSIC PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    return $arr;
  }

  /*
   * RETURN THE SOCIAL ENGINE CLASSIFIED PRIVACY VALUE
   */
  protected function _translateClassifiedPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE CLASSIFIED PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    return $arr;
  }

  /*
   * RETURN THE SOCIAL ENGINE EVENT PRIVACY VALUE
   */
  protected function _translateEventPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE EVENT PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    if( $value == 5 ) {
      $arr = array('member');
    }

    return $arr;
  }

  /*
   * RETURN THE SOCIAL ENGINE PARENT EVENT PRIVACY VALUE
   */
  protected function _translateParentEventPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE PARENT EVENT PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'member', 'member_requested', 'parent_member', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('member', 'member_requested', 'parent_member');
    }

    if( $value == 2 ) {
      $arr = array('member_requested');
    }

    return $arr;
  }

  /*
   * RETURN THE SOCIAL ENGINE ALBUM PRIVACY VALUE
   */
  protected function _translateAlbumPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE ALBUM PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    return $arr;
  }

  /*
   * RETURN THE SOCIAL ENGINE VIDEO PRIVACY VALUE
   */
  protected function _translateVideoPrivacy($value, $mode = null)
  {
    //MAP THE PHPFOX PRIVACY VALUE AND RETURN THE SOCIAL ENGINE VIDEO PRIVACY VALUE ARRAY
    $arr = array();
    if( $value == 0 || $value == 4 ) {
      $arr = array('everyone', 'owner_member', 'owner_member_member', 'owner_network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('owner_member');
    }

    if( $value == 2 ) {
      $arr = array('owner_member', 'owner_member_member');
    }

    return $arr;
  }

  /*
   * INSERT USER FIELD SEARCH
   */
  protected function _insertFieldSearch($data)
  {
    $this->getToDb()
      ->insert('engine4_user_fields_search', array
        (
        'item_id' => $data['item_id'],
        'profile_type' => $data['profile_type'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'gender' => $data['gender'],
        'birthdate' => $data['birthdate']
        )
    );
  }

  /*
   * FIND MEMBER TYPE
   */
  protected function findMemberType($id)
  {
    $bannedUserGroupId = $this->getParam('bannedUserGroupId');
    $adminUserGroupId = $this->getParam('adminUserGroupId');
    $registeredUserGroupId = $this->getParam('registeredUserGroupId');
    $guestUserGroupId = $this->getParam('guestUserGroupId');
    $staffUserGroupId = $this->getParam('staffUserGroupId');
    switch( $id ) {
      case $adminUserGroupId :
        return 'admin';
      case $registeredUserGroupId :
        return 'user';
      case $guestUserGroupId :
        return 'public';
      case $staffUserGroupId :
        //moderator type can be deleted so checking wheater exist or not
        $level = $this->getLevelId('moderator', '');
        if( $level === false || is_null($level) || empty($level) )
          return 'admin';
        return 'moderator';
      case $bannedUserGroupId :
        return 'user';
    }
    $grpDetail = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'user_group', array('title', 'inherit_id as id'))
      ->where('user_group_id = ?', $id)
      ->query()
      ->fetch();
    if( !is_null($grpDetail) && !empty($grpDetail) && count($grpDetail) > 0 && isset($grpDetail['title']) && strlen($grpDetail['title']) > 0 ) {
      switch( $grpDetail['title'] ) {
        case 'Administrator':
          return 'admin';
        case 'Registered User':
          return 'user';
        case 'Guest':
          return 'public';
        case 'Staff':
          //moderator type can be deleted so checking wheater exist or not
          $level = $this->getLevelId('moderator', '');
          if( $level === false || is_null($level) || empty($level) )
            return 'admin';
          return 'moderator';
        case 'Banned':
          return 'user';
        default :
          if( $grpDetail['id'] != 0 )
            return $this->findMemberType($grpDetail['id']);
          else
            return 'user';
      }
    }
    else {
      return 'user';
    }
  }

  /*
   * Fetching all permissions of $type and assigned into $toLevelId
   */
  protected function _insertPermissions($type, $toLevelId)
  {
    $fromLevelId = $this->getLevelId($type, '');
    if( $fromLevelId === false || is_null($fromLevelId) )
      return false;

    $this->getToDb()
      ->query(
        "insert ignore into engine4_authorization_permissions
                                (level_id,type,name,value,params)
                        select $toLevelId,type,name,value,params from engine4_authorization_permissions
                        where level_id=$fromLevelId
                        "
    );
  }

  //Insert authorization levels
  protected function _insertMemberLevel()
  {
    $bannedUserGroupId = $this->getParam('bannedUserGroupId');
    $adminUserGroupId = $this->getParam('adminUserGroupId');
    $registeredUserGroupId = $this->getParam('registeredUserGroupId');
    $guestUserGroupId = $this->getParam('guestUserGroupId');
    $staffUserGroupId = $this->getParam('staffUserGroupId');
    // Select authorization level
    $findMemberQuery = "select * FROM " . $this->getfromPrefix() . "user_group where title not in ('Administrator','Guest','Registered User','Staff','Banned')";
    $qryPart = array();
    $qryPart2 = array();

    if( !empty($bannedUserGroupId) )
      $qryPart[] = $bannedUserGroupId;
    else
      $qryPart2[] = "'Banned'";

    if( !empty($adminUserGroupId) )
      $qryPart[] = $adminUserGroupId;
    else
      $qryPart2[] = "'Administrator'";

    if( !empty($registeredUserGroupId) )
      $qryPart[] = $registeredUserGroupId;
    else
      $qryPart2[] = "'Registered User'";

    if( !empty($guestUserGroupId) )
      $qryPart[] = $guestUserGroupId;
    else
      $qryPart2[] = "'Guest'";

    if( !empty($staffUserGroupId) )
      $qryPart[] = $staffUserGroupId;
    else
      $qryPart2[] = "'Staff'";

    if( count($qryPart) > 0 && count($qryPart2) > 0 )
      $findMemberQuery = "select * FROM " . $this->getfromPrefix() . "user_group where title not in (" . implode(",", $qryPart2) . ") and user_group_id not in (" . implode(",", $qryPart) . ")";
    else if( count($qryPart) > 0 )
      $findMemberQuery = "select * FROM " . $this->getfromPrefix() . "user_group where  user_group_id not in (" . implode(",", $qryPart) . ")";
    else if( count($qryPart2) > 0 )
      $findMemberQuery = "select * FROM " . $this->getfromPrefix() . "user_group where title not in (" . implode(",", $qryPart2) . ") ";
    $members = $this->getFromDb()
      ->query($findMemberQuery)
      ->fetchAll();
    //Loop for Insertion  authorization levels 
    foreach( $members as $member ) {
      $isExistMemberLevel = $this->getToDb()->select()
        ->from('engine4_authorization_levels', 'level_id')
        ->where('trim(lower(title)) = ?', trim(strtolower($member['title'])))
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $isExistMemberLevel === false ) {
        $type = $this->findMemberType($member['inherit_id']);
        $this->getToDb()
          ->insert('engine4_authorization_levels', array
            (
            'title' => $member['title'],
            'type' => $type,
            )
        );
        $levelId = $this->getToDb()->lastInsertId();
        //assing the permissions
        $this->_insertPermissions($type, $levelId);
      }
    }
  }

  /*
   * Insert user other profile data
   * Setting the User cover photo,total view count,ip address,level and ban the user if he/she is banned user.
   */
  protected function _otherProfileData(&$data, $fromUserData)
  {
    $getFromDbPrefix = $this->getfromPrefix();
    //Select total view and cover photo
    $fromUserProfile = $this->getFromDb()->select()
      ->from($getFromDbPrefix . 'user_field', array('total_view', 'cover_photo', 'cover_photo_top'))
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetch();
    $data['view_count'] = (empty($fromUserProfile['total_view'])) ? 0 : ($fromUserProfile['total_view']);
    $user_cover = $fromUserProfile['cover_photo'];
    $cover_photo_top = $fromUserProfile['cover_photo_top'];
    //Select creation ip address
    $fromUserProfile = $this->getFromDb()->select()
      ->from($getFromDbPrefix . 'user_ip', 'ip_address')
      ->where('type_id = ?', 'register')
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetch();
    $data['creation_ip'] = (empty($fromUserProfile['ip_address'])) ? '0.0.0.0' : $fromUserProfile['ip_address'];
    $bannedUserGroupId = $this->getParam('bannedUserGroupId');
    $adminUserGroupId = $this->getParam('adminUserGroupId');
    $registeredUserGroupId = $this->getParam('registeredUserGroupId');
    $guestUserGroupId = $this->getParam('guestUserGroupId');
    $staffUserGroupId = $this->getParam('staffUserGroupId');
    //Select the authorization level
    if( !empty($fromUserData['user_group_id']) ) {
      $level = "";
      switch( $fromUserData['user_group_id'] ) {
        case $adminUserGroupId :
          $level = $this->getLevelId('admin', '');
          break;
        case $registeredUserGroupId :
          $level = $this->getLevelId('user', 'default');
          break;
        case $guestUserGroupId :
          $level = $this->getLevelId('public', 'public');
          break;
        case $staffUserGroupId :
          $level = $this->getLevelId('moderator', '');
          if( empty($level) )
            $level = $this->getLevelId('admin', '');
          break;
        case $bannedUserGroupId :
          $level = $this->getLevelId('user', 'default');
          // Banning the user.
          $data['approved'] = 0;
          $data['enabled'] = 0;
          break;
      }
      if( empty($level) ) {
        $groupName = $this->getFromDb()->select()
          ->from($getFromDbPrefix . 'user_group', 'title')
          ->where('user_group_id = ?', $fromUserData['user_group_id'])
          ->limit(1)
          ->query()
          ->fetchColumn(0);
        // Find the User Level id
        switch( $groupName ) {
          case 'Administrator':
            $level = $this->getLevelId('admin', '');
            break;
          case 'Registered User':
            $level = $this->getLevelId('user', 'default');
            break;
          case 'Guest':
            $level = $this->getLevelId('public', 'public');
            break;
          case 'Staff':
            $level = $this->getLevelId('moderator', '');
            if( empty($level) )
              $level = $this->getLevelId('admin', '');
            break;
          case 'Banned':
            $level = $this->getLevelId('user', 'default');
            // Banning the user.
            $data['approved'] = 0;
            $data['enabled'] = 0;
            break;
          default :
            $level = $this->getLevelIdByTitle($groupName);
            if( empty($level) )
              $level = $this->getLevelId('user', 'default');
        }
      }
    } else
      $level = $this->getLevelId('public', 'public');

    //Setting the user Level id.
    $data['level_id'] = $level;
    $userInfo = $data;
    $userInfo['cover_photo_top'] = $cover_photo_top;
    $userInfo['user_cover'] = $user_cover;
    // Insert the cover photo
    if( $this->_columnExist('engine4_users', 'user_cover') )
      $data['user_cover'] = $this->_insertCoverPhoto($userInfo);
  }

  /*
   * Insert User cover photo
   */
  public function _insertCoverPhoto($data)
  {
    // return 0 if there is no any cover photo for Selected user
    if( empty($data['user_cover']) )
      return 0;

    $coverParams = array('top' => 0, 'left' => 0);
    if( !empty($data['cover_photo_top']) )
      $coverParams = array('top' => $data['cover_photo_top'], 'left' => 0);
    $data['cover_params'] = Zend_Json_Encoder::encode($coverParams);
    $data['album_title'] = 'Cover Photos';
    $data['album_type'] = 'cover';
    $data['time_stamp'] = time();
    //Find album id
    $albumId = $this->findAlbum($data);
    $data['album_id'] = $albumId;
    $fromPhotoModel = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'photo', '*')
      ->where('photo_id = ?', $data['user_cover'])
      ->query()
      ->fetch();
    if( !$fromPhotoModel )
      return 0;
    $imgPath = $fromPhotoModel['destination'];
    $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
    $destinationPath = $dir . 'file/pic/photo' . DIRECTORY_SEPARATOR . $imgPath;
    $data['sourcePath'] = $destinationPath;
    //Insert cover photo
    $photoInfo = $this->savePhoto($data);
    return $photoInfo['photoId'];
  }

  /*
   * Find Level id by authorization type and authorization flag
   */
  public function getLevelId($type, $flag)
  {
    if( $type == 'admin' ) {
      $flag = 'admin';
    }
    if( !empty($flag) && strlen($flag) > 0 ) {
      if( $flag == 'admin' ) {
        $flag = '';
      }
      $levelId = $this->getToDb()->select()
        ->from('engine4_authorization_levels', 'level_id')
        ->where('type = ?', $type)
        ->where('flag = ?', $flag)
        ->order('level_id', 'ASC')
        ->limit(1)
        ->query()
        ->fetchColumn(0);
    } else {
      $levelId = $this->getToDb()->select()
        ->from('engine4_authorization_levels', 'level_id')
        ->where('type = ?', $type)
        ->order('level_id', 'ASC')
        ->limit(1)
        ->query()
        ->fetchColumn(0);
    }
    return $levelId;
  }

  /*
   * Find Level id by authorization title
   */
  public function getLevelIdByTitle($title)
  {
    $levelId = $this->getToDb()->select()
      ->from('engine4_authorization_levels', 'level_id')
      ->where('trim(lower(title)) = ?', trim(strtolower($title)))
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    return $levelId;
  }

  public function getUserGrpName($grpId)
  {
    $userGrpName = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'user_group', 'title')
      ->where('user_group_id = ? ', $grpId)
      ->query()
      ->fetchColumn(0);
    return $userGrpName;
  }

  /*
   *  Find Level id by title 
   */
  public function getLevelIdByTitleName($title)
  {

    $bannedUserGroupId = $this->getParam('bannedUserGroupId');
    $adminUserGroupId = $this->getParam('adminUserGroupId');
    $registeredUserGroupId = $this->getParam('registeredUserGroupId');
    $guestUserGroupId = $this->getParam('guestUserGroupId');
    $staffUserGroupId = $this->getParam('staffUserGroupId');
    $grpArr = array();
    if( !empty($bannedUserGroupId) )
      $grpArr[] = $bannedUserGroupId;

    if( !empty($adminUserGroupId) )
      $grpArr[] = $adminUserGroupId;

    if( !empty($registeredUserGroupId) )
      $grpArr[] = $registeredUserGroupId;

    if( !empty($guestUserGroupId) )
      $grpArr[] = $guestUserGroupId;

    if( !empty($staffUserGroupId) )
      $grpArr[] = $staffUserGroupId;
    $userGrps = array();
    if( count($grpArr) > 0 ) {
      $userGrpDtls = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'user_group', array('title', 'user_group_id'))
        ->where('user_group_id in (?) ', $grpArr)
        ->query()
        ->fetchAll();
      foreach( $userGrpDtls as $userGrpDtl ) {
        $userGrps[$userGrpDtl['user_group_id']] = $userGrpDtl['title'];
      }
    }
    if( !empty($bannedUserGroupId) ) {
      if( $title == $userGrps[$bannedUserGroupId] )
        return $this->getLevelId('user', 'default');
    }

    if( !empty($adminUserGroupId) ) {
      if( $title == $userGrps[$adminUserGroupId] )
        return $this->getLevelId('admin', '');
    }

    if( !empty($registeredUserGroupId) ) {
      if( $title == $userGrps[$registeredUserGroupId] )
        return $this->getLevelId('user', 'default');
    }

    if( !empty($guestUserGroupId) ) {
      if( $title == $userGrps[$guestUserGroupId] )
        return $this->getLevelId('public', 'public');
    }

    if( !empty($staffUserGroupId) ) {
      if( $title == $userGrps[$staffUserGroupId] ) {
        $lvl = $this->getLevelId('moderator', '');
        if( $lvl === false || is_null($lvl) || empty($lvl) )
          $level = $this->getLevelId('admin', '');
        else
          $level = $lvl;
        return $level;
      }
    }
    switch( $title ) {
      case 'Administrator':
        $level = $this->getLevelId('admin', '');
        break;
      case 'Registered User':
        $level = $this->getLevelId('user', 'default');
        break;
      case 'Guest':
        $level = $this->getLevelId('public', 'public');
        break;
      case 'Staff':
        $lvl = $this->getLevelId('moderator', '');
        if( $lvl === false || is_null($lvl) || empty($lvl) )
          $level = $this->getLevelId('admin', '');
        else
          $level = $lvl;
        break;
      case 'Banned':
        $level = $this->getLevelId('user', 'default');
        break;
      default :
        $lvl = $this->getLevelIdByTitle($title);
        if( $lvl === false || is_null($lvl) || empty($lvl) )
          $level = $this->getLevelId('user', 'default');
        else
          $level = $lvl;
    }
    return $level;
  }

  /*
   * GET USER ID OF SUPER ADMIN
   */
  public function getSuperAdminUserId()
  {
    // Check for user with same email
    $userIdentity = $this->getToDb()
      ->select()
      ->from('engine4_users', 'user_id')
      ->where('level_id = ?', 1)
      ->limit(1)
      ->query()
      ->fetchColumn(0)
    ;
    return $userIdentity;
  }

  /*
   * FIND FRIEND LIST COUNT
   */
  public function findFriendListCount($listId)
  {
    $count = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'friend_list_data', 'count(*)')
      ->where('list_id = ?', $listId)
      ->query()
      ->fetchColumn(0);
    if( $count === false || is_null($count) )
      $count = 0;
    return $count;
  }

  /*
   * THIS FUNCTION CONVERT SPECIAL TAGS TO HTML TAG
   */
  protected function convertTextTagtoHtmlTag($data)
  {
    $data = nl2br($data);
    //PREPARE AN ARRAY FROM TAG TO REPLACE WHICH TAG
    $tags = array
      (
      '[b]' => '<strong>',
      '[/b]' => '</strong>',
      '[i]' => '<em>',
      '[/i]' => '</em>',
      '[u]' => '<span style="text-decoration: underline;">',
      '[/u]' => '</span>',
      '[left]' => '<p style="text-align: left;">',
      '[/left]' => '</p>',
      '[center]' => '<p style="text-align: center;">',
      '[/center]' => '</p>',
      '[right]' => '<p style="text-align: right;">',
      '[/right]' => '</p>',
      '[&#42;]' => '<li>',
      '[ul]' => '<ul>',
      '[/ul]' => '</ul>',
      '[ol]' => '<ol>',
      '[/ol]' => '</ol>',
    );
    //LOOP FOR REPLACEING TAG
    foreach( $tags as $fromTag => $toTag )
      $data = str_ireplace($fromTag, $toTag, $data, $count);

    return $data;
  }

  /*
   * THIS FUNCTION USED TO GET BODY OF BLOG , EVENT,FORUM etc.
   * THIS FUNCTION ALSO RESPONSIBILTY TO CREATE IMAGE,LINK IF ANY.
   */
  protected function getBody($data)
  {
    if( !isset($data['categoryId']) )
      $data['categoryId'] = $data['album_type'];
    $attchIdArr = array();
    //CONVERTING THE TEXT INTO HTML TAGS
    $data['text'] = $this->convertTextTagtoHtmlTag($data['text']);
    $body = $data['text'];
    //FIND THE IMAGE SOURCE PATH
    $imagesArr = $this->getStringBetween($data['text'], "[img]", "[/img]");
    //INSERTING THE PHOTO
    foreach( $imagesArr as $imgPath ) {
      //FIND THE ALBUM ID
      $albumId = $this->findAlbum($data);
      $data['album_id'] = $albumId;
      if( strlen($imgPath) <= 0 )
        continue;
      $sArr = explode('/file/', $imgPath);
      $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
      //image not found in specified folder(ie customized and stored some other place)
      if( isset($sArr[1]) ) {

        $destinationPath = $dir . 'file' . DIRECTORY_SEPARATOR . $sArr[1];
        $data['sourcePath'] = $destinationPath;
        //INSERT THE PHOTO
        $photoInfo = $this->savePhoto($data);
        $path = $photoInfo['storagePath'];
        if( strlen($path) == 0 ) {
          $path = '<img src="' . $imgPath . '" alt="" >';
          $body = str_ireplace($imgPath, $path, $body, $count);
          continue;
        }
      } else
        $path = $imgPath;
      //CREATE THE IMG TAG AND REPLACE INTO BODY
      $path = '<img src="' . $path . '" alt="" >';
      $body = str_ireplace($imgPath, $path, $body, $count);
    }
    //REMOVING THE SPECIAL TAGS
    $body = str_ireplace("[img]", "", $body, $count);
    $body = str_ireplace("[/img]", "", $body, $count);
    //FIND IMAGE SOURCE PATH
    $imagesArr = $this->getStringBetween($data['text'], "&lt;img src=&quot;", "&quot; alt=&quot;&quot; /&gt;");
    //INSERTION OF PHOTO
    foreach( $imagesArr as $imgPath ) {
      //FIND THE ALBUM ID
      $albumId = $this->findAlbum($data);
      $data['album_id'] = $albumId;
      if( strlen($imgPath) <= 0 )
        continue;
      $sArr = explode('/file/', $imgPath);
      $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
      if( isset($sArr[1]) ) {
        $destinationPath = $dir . 'file' . DIRECTORY_SEPARATOR . $sArr[1];
        $data['sourcePath'] = $destinationPath;
        //SAVING THE PHOTO
        $photoInfo = $this->savePhoto($data);
        $path = $photoInfo['storagePath'];
        if( strlen($path) == 0 ) {
          $path = '<img src="' . $imgPath . '" alt="" >';
          $body = str_ireplace($imgPath, $path, $body, $count);
          continue;
        }
      } else
        $path = $imgPath;

      //BUILDING THE IMAGE TAG AND PLACE IT INTO BODY
      $path = '<img src="' . $path . '" alt="" >';
      $body = str_ireplace($imgPath, $path, $body, $count);
    }
    //REMOVE THE SPECIAL TAGS FROM BODY.
    $body = str_ireplace("&lt;img src=&quot;", "", $body, $count);
    $body = str_ireplace("&quot; alt=&quot;&quot; /&gt;", "", $body, $count);

    //FIND ALL ATTACHMENT
    $attachmentsArr = $this->getStringBetween($data['text'], "[attachment=&quot;", "&quot;]");
    //INSERT THE ATTACHMENTS
    foreach( $attachmentsArr as $attachment ) {
      $atchArr = explode(":", $attachment);
      if( count($atchArr) == 2 )
        $attachmentId = $atchArr[0];
      else
        $attachmentId = $attachment;
      $attchIdArr[] = $attachmentId;
      //FIND THE DETAIL OF ATTACHMENT
      $attachmentModel = $this->getFromDb()
        ->select()
        ->from($this->getfromPrefix() . 'attachment', '*')
        ->where('attachment_id = ?', $attachmentId)
        ->query()
        ->fetch();
      //IF NO DETAIL FOUND FOR ATTACHMENT THEN CONTINUE THE LOOP
      if( $attachmentModel === false || is_null($attachmentModel) || count($attachmentModel) == 0 )
        continue;

      $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
      $destinationPath = $dir . 'file/attachment' . DIRECTORY_SEPARATOR . $attachmentModel['destination'];

      $imgPath = $destinationPath;
      if( count($atchArr) == 2 )
        $attachment = '[attachment=&quot;' . $attachment . '&quot;]' . $attachmentModel['file_name'];
      else
        $attachment = '[attachment=&quot;' . $attachment . '&quot;]';

      //FIND TYPE OF ATTACHMENT
      if( $attachmentModel['is_image'] == 1 ) {
        //FIND ALBUM
        $albumId = $this->findAlbum($data);
        $data['album_id'] = $albumId;

        $data['sourcePath'] = $imgPath;
        //INSERT PHOTO
        $photoInfo = $this->savePhoto($data);
        $path = $photoInfo['storagePath'];
        if( strlen($path) == 0 ) {
          $path = '<img src="' . $imgPath . '" alt="" >';
          $body = str_ireplace($attachment, $path, $body, $count);
          continue;
        }
        $dir = $this->getToPath() . DIRECTORY_SEPARATOR;
        //BUILDING PHOTO AND PLACE IT INTO BODY
        $path = '<img src="' . $path . '" alt="" >';
        $body = str_ireplace($attachment, $path, $body, $count);
      } else if( $attachmentModel['is_video'] == 1 ) {
        //ATTACHMENT TYPE VIDEO THEN ESCAPE THAT VIDEO . 
        $body = str_ireplace($attachment, "", $body, $count);
      } else {
        // it will come here if file is zip or other than images and video.
      }
    }
    //REMOVE SPECIAL TAGS FROM BODY
    $body = str_ireplace("[/attachment]", "", $body, $count);
    //FIND ALL OTHER ATTACHMENT(EXCLUDING ATTACHMENT WHICH ARE ALREADY TAKEN)
    if( count($attchIdArr) > 0 ) {
      $attachmentsModel = $this->getFromDb()
        ->select()
        ->from($this->getfromPrefix() . 'attachment', '*')
        ->where('item_id = ?', $data['item_id'])
        ->where('attachment_id not in( ? )', $attchIdArr)
        ->where('category_id = ?', $data['categoryId'])
        ->query()
        ->fetchAll();
    } else {
      $attachmentsModel = $this->getFromDb()
        ->select()
        ->from($this->getfromPrefix() . 'attachment', '*')
        ->where('item_id = ?', $data['item_id'])
        ->where('category_id = ?', $data['album_type'])
        ->query()
        ->fetchAll();
    }
    //TRAVERSING THE ATTACHMENT
    foreach( $attachmentsModel as $attachmentModel ) {
      $imgPath = $attachmentModel['destination'];
      $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
      $destinationPath = $dir . 'file/attachment' . DIRECTORY_SEPARATOR . $attachmentModel['destination'];
      //FIND THE TYPE OF ATTACHMENT
      if( $attachmentModel['is_image'] ) {
        //FIND ALBUM
        $albumId = $this->findAlbum($data);
        $data['album_id'] = $albumId;
        $data['sourcePath'] = $destinationPath;
        //SAVE PHOTO
        $photoInfo = $this->savePhoto($data);
        $path = $photoInfo['storagePath'];
        if( strlen($path) == 0 ) {
          continue;
        }
        $dir = $this->getToPath() . DIRECTORY_SEPARATOR;
        //BUILD THE IMAGE TAG AND PLACE IT INTO BODY
        $path = '<img src="' . $path . '" alt="" >';
        $body .= $path;
      } else if( $attachmentModel['is_video'] ) {
        //ESCAPE VIDEO
      } else if( $attachmentModel['link_id'] != 0 ) {
        //FIND LINK DETAIL
        $linkModel = $this->getFromDb()
          ->select()
          ->from($this->getfromPrefix() . 'link', '*')
          ->where('link_id = ?', $attachmentModel['link_id'])
          ->query()
          ->fetch();
        $lnk = explode("http://", $linkModel['link']);
        $lnk1 = explode("https://", $linkModel['link']);
        //BUILDING A CORRECT URL
        if( count($lnk) < 2 ) {
          if( count($lnk1) < 2 )
            $linkPath = "http://" . $linkModel['link'];
          else
            $linkPath = $linkModel['link'];
        } else
          $linkPath = $linkModel['link'];

        $linkDesc = is_null($linkModel['description']) ? '' : $linkModel['description'];
        //BUILDING THE ANCHOR TAG AND PLACE IT INTO BODY.
        $linkHtmlCode = '<p><a href="' . $linkPath . '">' . $linkModel['title'] . '</a><br /><div>' . $linkDesc . '</div></p>';
        $body .= $linkHtmlCode;
      }
      else {
        // it will come here if file is zip or other than images and video.
      }
    }

    //Quote parsing
    $quoteArr = $this->getStringBetween($data['text'], "[quote=", "]");
    foreach( $quoteArr as $qt ) {

      if( empty($qt) )
        continue;
      //FIND USER FULL NAME
      $userFullName = $this->getFromDb()
        ->select()
        ->from($this->getfromPrefix() . 'user', 'full_name')
        ->where('user_id = ?', $qt)
        ->query()
        ->fetchColumn(0);
      //REPLACING THE SPECIAL TAGS TO HTML TAGS
      $fromReplace = '[quote=' . $qt . ']';
      $toReplace = '<p>[blockquote][b]' . $userFullName . " said:[/b]</p>";
      $body = str_ireplace($fromReplace, $toReplace, $body, $count);
    }
    $body = str_ireplace("[/quote]", "<p>[/blockquote]</p>", $body, $count);
    // $body = htmlspecialchars_decode($body);
    $body = mb_convert_encoding($body, "UTF-8", "HTML-ENTITIES");
    return $body;
  }

  /*
   * FIND OR CREATE SE ALBUM ID
   */
  public function findSEAlbum($data, $isSearchable = '1')
  {
    if( !isset($data['cover_params']) )
      $data['cover_params'] = null;
    $albumId = $this->getToDb()
      ->select()
      ->from('engine4_album_albums', 'album_id')
      ->where('title = ?', $data['album_title'])
      ->where('owner_type = ?', 'user')
      ->where('owner_id = ?', $data['user_id'])
      ->limit(1)
      ->query()
      ->fetchColumn(0);

    if( !$albumId ) {
      $maxId = $this->getToDb()
        ->select()
        ->from('engine4_album_albums', 'max(album_id)')
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $maxId === false || $maxId < 1000000 )
        $maxId = 1000000;
      else
        $maxId++;

      //PREPARING ALBUM ARRAY
      $albumData = array(
        'album_id' => $maxId,
        'title' => $data['album_title'],
        'description' => $data['album_title'],
        'owner_type' => 'user',
        'owner_id' => $data['user_id'],
        'type' => $data['album_type'],
        'search' => $isSearchable,
        'creation_date' => $this->_translateTime($data['time_stamp']),
        'modified_date' => $this->_translateTime($data['time_stamp'])
      );
      //CHECKING FOR cover_params column
      if( $this->_columnExist('engine4_album_albums', 'cover_params') ) {
        $albumData['cover_params'] = $data['cover_params'];
      }
      //INSERT ALBUM
      $this->getToDb()->insert('engine4_album_albums', $albumData);
      $albumId = $this->getToDb()->lastInsertId();
    }
    return $albumId;
  }

  /*
   * CREATE OR FIND GROUP ALBUM
   */
  public function findGroupAlbum($data)
  {
    $albumId = $this->getToDb()
      ->select()
      ->from('engine4_group_albums', 'album_id')
      ->where('group_id = ?', $data['page_id'])
      ->limit(1)
      ->query()
      ->fetchColumn(0);

    if( !$albumId ) {
      $maxId = $this->getToDb()
        ->select()
        ->from('engine4_group_albums', 'max(album_id)')
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $maxId === false || $maxId < 1000000 )
        $maxId = 1000000;
      else
        $maxId++;
      //INSERT THE GROUP ALBUM
      $this->getToDb()->insert('engine4_group_albums', array(
        'album_id' => $maxId,
        'title' => $data['album_title'],
        'description' => $data['album_title'],
        'group_id' => $data['page_id'],
        'creation_date' => $this->_translateTime($data['time_stamp']),
        'modified_date' => $this->_translateTime($data['time_stamp']),
      ));
      $albumId = $this->getToDb()->lastInsertId();
    }
    return $albumId;
  }

  /*
   * FIND THE ALBUM ID ACCOURDING TO ALBUM TABLE
   */
  public function findAlbum($data, $isSearchable = '1')
  {

    if( !isset($data['album_table']) )
      $data['album_table'] = '';
    switch( $data['album_table'] ) {
      case 'group_albums' :
        $albumId = $this->findGroupAlbum($data);
        break;
      default : $albumId = $this->findSEAlbum($data, $isSearchable);
    }
    return $albumId;
  }

  /*
   * This function used to save the photo.
   */
  protected function savePhoto($data)
  {
    $photoData = array('storagePath' => '', 'photoId' => '');
    $destinationPath = $data['sourcePath'];
    $des = explode('%s', $destinationPath);
    $destinationPath = $des[0];
    if( isset($des[1]) )
      $destinationPath = $des[0] . $des[1];
    if( $destinationPath ) {

      $photoArr = array
        (
        'title' => '',
        'creation_date' => $this->_translateTime($data['time_stamp']),
        'modified_date' => $this->_translateTime($data['time_stamp']),
        'album_id' => $data['album_id'],
      );

      if( !isset($data['album_table']) )
        $data['album_table'] = '';
      switch( $data['album_table'] ) {
        case 'group_albums' :
          $photoArr['user_id'] = $data['user_id'];
          $photoArr['group_id'] = $data['page_id'];
          $photoArr['collection_id'] = $data['album_id'];
          $photoTableName = "engine4_group_photos";
          $storageType = "album_photo";
          $albumTable = "engine4_group_albums";
          break;
        default :
          $photoArr['owner_id'] = $data['user_id'];
          $photoArr['owner_type'] = 'user';
          $storageType = "album_photo";
          $photoTableName = "engine4_album_photos";
          $albumTable = "engine4_album_albums";
      }

      $file = $destinationPath;
      try {
        //Insert data into engine4_storage_files table
        $file_id = $this->_translateFile($file, array(
          'parent_type' => $storageType,
          'parent_id' => $data['album_id'],
          'user_id' => $data['user_id'],
          ), true);
        //Select photo id
        $maxId = $this->getToDb()
          ->select()
          ->from($photoTableName, 'max(photo_id)')
          ->limit(1)
          ->query()
          ->fetchColumn(0);
        if( $maxId === false || $maxId < 1000000 )
          $maxId = 1000000;
        else
          $maxId++;
        //Insert data in engine4_album_photos table
        $photoArr['photo_id'] = $maxId;
        $photoArr['file_id'] = $file_id;
        $this->getToDb()
          ->insert($photoTableName, $photoArr);

        $photoId = $this->getToDb()->lastInsertId();
        $storagePath = $this->getToDb()->select()
          ->from('engine4_storage_files', 'storage_path')
          ->where('file_id = ?', $file_id)
          ->query()
          ->fetchColumn();
        // Update the album
        if( $albumTable == 'engine4_album_albums' ) {
          if( $this->_columnExist('engine4_album_albums', 'photos_count') ) {
            $this->getToDb()->update($albumTable, array('photos_count' => new Zend_Db_Expr('photos_count + 1')), array('album_id=?' => $data['album_id'])
            );
          }
        }
        $photoData['storagePath'] = $storagePath;
        $photoData['photoId'] = $photoId;
      } catch( Exception $e ) {
        $file_id = null;
        $this->_logFile($e->getMessage());
      }
    }
    return $photoData;
  }

  /*
   * return content between the start text and end text.
   */
  public function getStringBetween($string, $start, $end)
  {
    $last_end = 0;
    $matches = array();
    while( ($ini = strpos($string, $start, $last_end)) !== false ) {

      $ini += strlen($start);
      $len = strpos($string, $end, $ini);
      if( $len === false )
        break;
      $len = $len - $ini;
      $matches[] = substr($string, $ini, $len);
      $last_end = $ini + $len + strlen($end);
    }
    return $matches;
  }

  /*
   * FIND THE BODY FOR MESSAGE. 
   * THIS WILL ATTACH THE LINK AND IMAGE IF NEEDED.
   */
  protected function attachOtherMessage($data, $isSearchable = '1')
  {
    //convert special tags used in phpfox to html tag
    $data['text'] = $this->convertTextTagtoHtmlTag($data['text']);
    $data['text'] = str_ireplace(' class="parsed_image"', '', $data['text'], $count);
    $body = $data['text'];
    //find the path between the [img],[/img]

    $messageArr = array();
    $messageArr = array('fAttachmentId' => null, 'fAttachmentType' => null, 'body' => '');
    $mArr = array();
    //find the path between the '"<img src=";"' ,'"alt="">'
    $imagesArr = $this->getStringBetween($data['text'], '<img src="', ' alt="');
    //Traverse for each image path
    foreach( $imagesArr as $imgPath ) {
      $albumId = $this->findAlbum($data, $isSearchable);
      $data['album_id'] = $albumId;
      if( strlen($imgPath) <= 0 )
        continue;
      $sArr = explode('/file/', $imgPath);
      $pth = $sArr[0];
      if( isset($sArr[1]) )
        $pth = $sArr[1];
      $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
      $destinationPath = $dir . 'file' . DIRECTORY_SEPARATOR . $pth;
      $data['sourcePath'] = $destinationPath;
      $photoInfo = $this->savePhoto($data);
      $photoId = $photoInfo['photoId'];
      if( strlen($photoId) == 0 )
        continue;
      $path = $photoInfo['storagePath'];
      if( strlen($path) == 0 )
        continue;
      $body = str_ireplace($imgPath, $path, $body, $count);
    }
    $imagesArr = $this->getStringBetween($data['text'], '<span id="js_attachment_id_', '" /></a></span>');
    //TRAVERSE FOR EACH VIDEO
    foreach( $imagesArr as $imgPath ) {
      //REMOVE VIDEO
      $body = str_ireplace($imgPath, "", $body, $count);
    }
    //REPLACE THE SPECIAL TAGS
    $body = str_ireplace('<span id="js_attachment_id_', "", $body, $count);
    $body = str_ireplace('" /></a></span>', "", $body, $count);
    //FIND THE ATTACHMENTS
    $attachmentsModel = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'attachment', '*')
      ->where('item_id = ?', $data['item_id'])
      ->where('category_id = ?', $data['category_id'])
      ->query()
      ->fetchAll();
    //INSERTION OF EACH ATTACHMENT
    foreach( $attachmentsModel as $attachmentModel ) {
      $imgPath = $attachmentModel['destination'];
      $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
      $destinationPath = $dir . 'file/attachment' . DIRECTORY_SEPARATOR . $attachmentModel['destination'];
      //FIND THE ATTACHMENT TYPE
      if( $attachmentModel['is_image'] ) {
        //FIND ALBUM ID
        $albumId = $this->findAlbum($data, $isSearchable);
        $data['album_id'] = $albumId;
        $data['sourcePath'] = $destinationPath;
        //SAVE PHOTO
        $photoInfo = $this->savePhoto($data);
        $path = $photoInfo['storagePath'];
        if( strlen($path) == 0 ) {
          continue;
        }
        $dir = $this->getToPath() . DIRECTORY_SEPARATOR;
        //BUILDING THE IMAGE TAG AND PLACE IT INTO BODY
        $path = '<img src="' . $path . '" alt="" >';
        $body .= $path;
      } else if( $attachmentModel['is_video'] ) {
        //ESCAPE THE VIDEO
      } else if( $attachmentModel['link_id'] != 0 ) {
        //FIND THE LINK DETAIL
        $linkModel = $this->getFromDb()
          ->select()
          ->from($this->getfromPrefix() . 'link', '*')
          ->where('link_id = ?', $attachmentModel['link_id'])
          ->query()
          ->fetch();
        //BUILD CORRECT URL
        $lnk = explode("http://", $linkModel['link']);
        $lnk1 = explode("https://", $linkModel['link']);
        if( count($lnk) < 2 ) {
          if( count($lnk1) < 2 )
            $linkPath = "http://" . $linkModel['link'];
          else
            $linkPath = $linkModel['link'];
        } else
          $linkPath = $linkModel['link'];

        $linkDesc = is_null($linkModel['description']) ? '' : $linkModel['description'];
        //BUILD THE ANCHOR TAG AND PLACE IT INTO BODY
        $linkHtmlCode = '<p><a href="' . $linkPath . '">' . $linkModel['title'] . '</a><br /><div>' . $linkDesc . '</div></p>';
        $body .= $linkHtmlCode;
      }
      else {
        // it will come here if file is zip or other than images and video.
      }
    }



    $body = mb_convert_encoding($body, "UTF-8", "HTML-ENTITIES");
    $messageArr['body'] = is_null($body) ? '' : $body;
    $messageArr['fAttachmentId'] = is_null($messageArr['fAttachmentId']) ? 0 : $messageArr['fAttachmentId'];
    $messageArr['fAttachmentType'] = is_null($messageArr['fAttachmentType']) ? '' : $messageArr['fAttachmentType'];

    return $messageArr;
  }

  protected function _insertMessagesRecipients($data)
  {
    $senderMsg = $this->getToDb()
      ->select()
      ->from('engine4_messages_messages', array('max(message_id) AS id', 'date'))
      ->where('conversation_id = ?', $data['mail_id'])
      ->where('user_id = ?', $data['owner_user_id'])
      ->query()
      ->fetch();

    $viewerMsg = $this->getToDb()
      ->select()
      ->from('engine4_messages_messages', array('max(message_id) as id', 'date'))
      ->where('conversation_id = ?', $data['mail_id'])
      ->where('user_id = ?', $data['viewer_user_id'])
      ->query()
      ->fetch();

    if( $senderMsg === false || is_null($senderMsg) ) {
      $senderMsg = array();
      $senderMsg['id'] = null;
      $senderMsg['date'] = null;
    }
    if( $viewerMsg === false || is_null($viewerMsg) ) {
      $viewerMsg = array();
      $viewerMsg['id'] = null;
      $viewerMsg['date'] = null;
    }
    $this->getToDb()
      ->insert('engine4_messages_recipients', array
        (
        'user_id' => $data['owner_user_id'],
        'conversation_id' => $data['mail_id'],
        'inbox_message_id' => $viewerMsg['id'],
        'inbox_updated' => $viewerMsg['date'],
        'inbox_read' => 1,
        'inbox_deleted' => 1,
        'outbox_message_id' => $senderMsg['id'],
        'outbox_updated' => $senderMsg['date'],
        'outbox_deleted' => $data['owner_type_id']
        )
    );
    $this->getToDb()
      ->insert('engine4_messages_recipients', array
        (
        'user_id' => $data['viewer_user_id'],
        'conversation_id' => $data['mail_id'],
        'inbox_message_id' => $senderMsg['id'],
        'inbox_updated' => $senderMsg['date'],
        'inbox_read' => 0,
        'inbox_deleted' => $data['viewer_type_id'],
        'outbox_message_id' => $viewerMsg['id'],
        'outbox_updated' => $viewerMsg['date'],
        'outbox_deleted' => 1
        )
    );
  }

  public function findConversationId($mailId)
  {
    $mailModel = $this->getFromDb()
      ->select()
      ->from($this->getFromPrefix() . 'mail', array('mail_id', 'parent_id'))
      ->where('mail_id = ?', $mailId)
      ->query()
      ->fetch();

    if( $mailModel === false || is_null($mailModel) )
      return null;

    if( $mailModel['parent_id'] == 0 )
      return $mailModel['mail_id'];
    else
      return $this->findConversationId($mailModel['parent_id']);
  }

  /*
   * This function used to send message
   */
  public function createMessage($data)
  {
    $newData = array();
    $newData['conversation_id'] = $data['conversation_id'];
    $newData['title'] = $data['title'];
    $newData['user_id'] = $data['user_id'];
    $newData['date'] = $this->_translateTime($data['time_stamp']);
    $newData['body'] = '';
    $newData['attachment_type'] = $data['attachmentType'];
    $newData['attachment_id'] = $data['attachmentId'];
    $this->getToDb()
      ->insert('engine4_messages_messages', $newData);
    $messageId = $this->getToDb()->lastInsertId();
    return $messageId;
  }

  /*
   * This function used to send message if there are any attachment done in Phpfox messages
   */
//    protected function attachMessages($data) {
//        //convert special tags used in phpfox to html tag
//        $data['text'] = $this->convertTextTagtoHtmlTag($data['text']);
//        $body = $data['text'];
//        //find the path between the [img],[/img]
//        $imagesArr = $this->getStringBetween($data['text'], "[img]", "[/img]");
//        $messageArr = array();
//        $messageArr = array('fAttachmentId' => null, 'fAttachmentType' => null, 'body' => '');
//        $mArr = array();
//        //Traverse for each image path
//        foreach ($imagesArr as $imgPath) {
//            //get album id
//            $albumId = $this->findAlbum($data);
//            $data['album_id'] = $albumId;
//            if (strlen($imgPath) <= 0)
//                continue;
//
//            $sArr = explode('/file/', $imgPath);
//            //Destination path
//            $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
//            $destinationPath = $dir . 'file' . DIRECTORY_SEPARATOR . $sArr[1];
//            $data['sourcePath'] = $destinationPath;
//            $photoInfo = $this->savePhoto($data);
//            $photoId = $photoInfo['photoId'];
//            if (strlen($photoId) == 0)
//                continue;
//            $data['attachmentId'] = $photoId;
//            $data['attachmentType'] = 'album_photo';
//            $body = str_ireplace($imgPath, '', $body, $count);
//            if (is_null($messageArr['fAttachmentId']) && is_null($messageArr['fAttachmentType'])) {
//                $messageArr['fAttachmentId'] = $photoId;
//                $messageArr['fAttachmentType'] = 'album_photo';
//            } else {
//                $mid = $this->createMessage($data);
//                if (!is_null($mid))
//                    $mArr[] = $mid;
//            }
//        }
//
//        $body = str_ireplace("[img]", "", $body, $count);
//        $body = str_ireplace("[/img]", "", $body, $count);
//        //find the path between the '"<img src=";"' ,'"alt="">'
//        $imagesArr = $this->getStringBetween($data['text'], "&lt;img src=&quot;", "&quot; alt=&quot;&quot; /&gt;");
//        //Traverse for each image path
//        foreach ($imagesArr as $imgPath) {
//            $albumId = $this->findAlbum($data);
//            $data['album_id'] = $albumId;
//            if (strlen($imgPath) <= 0)
//                continue;
//            $sArr = explode('/file/', $imgPath);
//            $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
//            $destinationPath = $dir . 'file' . DIRECTORY_SEPARATOR . $sArr[1];
//            $data['sourcePath'] = $destinationPath;
//            $photoInfo = $this->savePhoto($data);
//            $photoId = $photoInfo['photoId'];
//            if (strlen($photoId) == 0)
//                continue;
//            $data['attachmentId'] = $photoId;
//            $data['attachmentType'] = 'album_photo';
//            $body = str_ireplace($imgPath, '', $body, $count);
//            if (is_null($messageArr['fAttachmentId']) && is_null($messageArr['fAttachmentType'])) {
//                $messageArr['fAttachmentId'] = $photoId;
//                $messageArr['fAttachmentType'] = 'album_photo';
//            } else {
//                $mid = $this->createMessage($data);
//                if (!is_null($mid))
//                    $mArr[] = $mid;
//            }
//        }
//        $body = str_ireplace("&lt;img src=&quot;", "", $body, $count);
//        $body = str_ireplace("&quot; alt=&quot;&quot; /&gt;", "", $body, $count);
//        $attachmentsArr = $this->getStringBetween($data['text'], "[attachment=&quot;", "&quot;]");
//        foreach ($attachmentsArr as $attachment) {
//            $atchArr = explode(":", $attachment);
//            if (count($atchArr) == 2)
//                $attachmentId = $atchArr[0];
//            else
//                $attachmentId = $attachment;
//            $attchIdArr[] = $attachmentId;
//            $attachmentModel = $this->getFromDb()
//                    ->select()
//                    ->from('phpfox_attachment', '*')
//                    ->where('attachment_id = ?', $attachmentId)
//                    ->query()
//                    ->fetch();
//            if ($attachmentModel === false || is_null($attachmentModel) || count($attachmentModel) == 0)
//                continue;
//
//            $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
//            $destinationPath = $dir . 'file/attachment' . DIRECTORY_SEPARATOR . $attachmentModel['destination'];
//
//            $imgPath = $destinationPath;
//            if (count($atchArr) == 2)
//                $attachment = '[attachment=&quot;' . $attachment . '&quot;]' . $attachmentModel['file_name'];
//            else
//                $attachment = '[attachment=&quot;' . $attachment . '&quot;]';
//            if ($attachmentModel['is_image'] == 1) {
//                $albumId = $this->findAlbum($data);
//                $data['album_id'] = $albumId;
//
//                $data['sourcePath'] = $imgPath;
//                $photoInfo = $this->savePhoto($data);
//                $photoId = $photoInfo['photoId'];
//                if (strlen($photoId) == 0)
//                    continue;
//                $data['attachmentId'] = $photoId;
//                $data['attachmentType'] = 'album_photo';
//
//                $body = str_ireplace($attachment, '', $body, $count);
//
//                if (is_null($messageArr['fAttachmentId']) && is_null($messageArr['fAttachmentType'])) {
//                    $messageArr['fAttachmentId'] = $photoId;
//                    $messageArr['fAttachmentType'] = 'album_photo';
//                } else {
//                    $mid = $this->createMessage($data);
//                    if (!is_null($mid))
//                        $mArr[] = $mid;
//                }
//            }
//            else if ($attachmentModel['is_video'] == 1) {
//                $body = str_ireplace($attachment, "", $body, $count);
//            } else {
//                // it will come here if file is zip or other than images and video.
//            }
//        }
//
//        $body = str_ireplace("[/attachment]", "", $body, $count);
//
//        if (count($attchIdArr) > 0) {
//            $attachmentsModel = $this->getFromDb()
//                    ->select()
//                    ->from('phpfox_attachment', '*')
//                    ->where('item_id = ?', $data['item_id'])
//                    ->where('attachment_id not in( ? )', $attchIdArr)
//                    ->where('category_id = ?', 'mail')
//                    ->query()
//                    ->fetchAll();
//        } else {
//            $attachmentsModel = $this->getFromDb()
//                    ->select()
//                    ->from('phpfox_attachment', '*')
//                    ->where('item_id = ?', $data['item_id'])
//                    ->where('category_id = ?', 'mail')
//                    ->query()
//                    ->fetchAll();
//        }
//        foreach ($attachmentsModel as $attachmentModel) {
//            $imgPath = $attachmentModel['destination'];
//            $attachment = '[attachment=&quot;' . $attachment . '&quot;]';
//            $dir = $this->getFromPath() . DIRECTORY_SEPARATOR;
//            $destinationPath = $dir . 'file/attachment' . DIRECTORY_SEPARATOR . $attachmentModel['destination'];
//            if ($attachmentModel['is_image'] == 1) {
//                $albumId = $this->findAlbum($data);
//                $data['album_id'] = $albumId;
//                $data['sourcePath'] = $destinationPath;
//                $photoInfo = $this->savePhoto($data);
//                $photoId = $photoInfo['photoId'];
//                if (strlen($photoId) == 0)
//                    continue;
//                $data['attachmentId'] = $photoId;
//                $data['attachmentType'] = 'album_photo';
//                if (is_null($messageArr['fAttachmentId']) && is_null($messageArr['fAttachmentType'])) {
//                    $messageArr['fAttachmentId'] = $photoId;
//                    $messageArr['fAttachmentType'] = 'album_photo';
//                } else {
//                    $mid = $this->createMessage($data);
//                    if (!is_null($mid))
//                        $mArr[] = $mid;
//                }
//            }
//            else if ($attachmentModel['is_video']) {
//                
//            } else if ($attachmentModel['link_id'] != 0) {
//                $linkModel = $this->getFromDb()
//                        ->select()
//                        ->from('phpfox_link', '*')
//                        ->where('link_id = ?', $attachmentModel['link_id'])
//                        ->query()
//                        ->fetch();
//                $lnk = explode("http://", $linkModel['link']);
//                $lnk1 = explode("https://", $linkModel['link']);
//                if (count($lnk) < 2) {
//                    if (count($lnk1) < 2)
//                        $linkPath = "http://" . $linkModel['link'];
//                    else
//                        $linkPath = $linkModel['link'];
//                } else
//                    $linkPath = $linkModel['link'];
//
//                $this->getToDb()
//                        ->insert('engine4_core_links', array
//                            (
//                            'link_id' => $attachmentModel['link_id'],
//                            'uri' => $linkPath,
//                            'title' => $linkModel['title'],
//                            'description' => $linkModel['description'],
//                            'parent_type' => 'user',
//                            'parent_id' => $data['user_id'],
//                            'owner_type' => 'user',
//                            'owner_id' => $data['user_id'],
//                            'creation_date' => $this->_translateTime($data['time_stamp'])
//                                )
//                );
//                $linkId = $attachmentModel['link_id'];
//                $data['attachmentId'] = $linkId;
//                $data['attachmentType'] = 'core_link';
//                if (is_null($messageArr['fAttachmentId']) && is_null($messageArr['fAttachmentType'])) {
//                    $messageArr['fAttachmentId'] = $linkId;
//                    $messageArr['fAttachmentType'] = 'core_link';
//                } else {
//                    $mid = $this->createMessage($data);
//                    if (!is_null($mid))
//                        $mArr[] = $mid;
//                }
//            }
//            else {
//                // it will come here if file is zip or other than images and video.
//            }
//        }
//        $body = htmlspecialchars_decode($body);
//        foreach ($mArr as $mId) {
//            $this->getToDb()->update('engine4_messages_messages', array
//                (
//                'body' => $body,
//                    ), array
//                (
//                'message_id = ?' => $mId,
//            ));
//        }
//        $messageArr['body'] = is_null($body) ? '' : $body;
//        $messageArr['fAttachmentId'] = is_null($messageArr['fAttachmentId']) ? 0 : $messageArr['fAttachmentId'];
//        $messageArr['fAttachmentType'] = is_null($messageArr['fAttachmentType']) ? '' : $messageArr['fAttachmentType'];
//        return $messageArr;
//    }

  /*
   * MAPPING USER PRIVACY
   */

  protected function _translateUserPrivacy($value)
  {
    $arr = array('everyone', 'member', 'network', 'registered');
    if( $value == 0 ) {
      $arr = array('everyone', 'member', 'network', 'registered');
    }

    if( $value == 1 ) {
      $arr = array('member', 'network', 'registered');
    }

    if( $value == 2 ) {
      $arr = array('member');
    }

    return $arr;
  }

  // Page map
  public function getPageMap($key)
  {

    $title = $this->getFromDb()
      ->select()->from($this->getfromPrefix() . 'page', 'title_url')
      ->where('page_id = ?', $key)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    //$name = str_replace('.', '_', $title);
    $pageId = $this->getToDb()->select()
      ->from('engine4_core_pages', 'page_id')
      ->where('title = ?', $title)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $pageId ) {
      return $pageId;
    } else {
      throw new Engine_Exception('No group mapping detected');
    }
  }

  /*
   * RETURN THE GROUP PRIVACY
   */
  protected function _translateGroupPrivacy($value, $mode = null, $role_id, $type)
  {
    $arr = array();
    switch( $type ) {
      case 'view':
        if( $value == 0 ) {
          $arr = array($role_id => 'group_list', 'member_requested', 'member', 'registered', 'everyone');
        }
        break;
      case 'photo':
      case 'event':
      case 'comment':
        if( $value == 0 ) {
          $arr = array($role_id => 'group_list', 'member_requested', 'member', 'registered');
        } else if( $value == 1 ) {
          $arr = array($role_id => 'group_list', 'member', 'member_requested');
        } else if( $value == 2 ) {
          $arr = array($role_id => 'group_list', 'member_requested');
        }
        break;
      case 'invite':
        $arr = array($role_id => 'group_list', 'member_requested', 'member');
        break;
    }

    if( $type == 'view' && ($value == 3 || $value == 1 || $value == 2) ) {
      $arr = array($role_id => 'group_list', 'member_requested', 'member');
    }
    return $arr;
  }

  /*
   * This function used to get project directory URL
   */
  protected function getApplicationDirUrl()
  {
    $url = 'http://' . $_SERVER['HTTP_HOST'] . str_replace('\\', '/', dirname(dirname($_SERVER['PHP_SELF'])));
    return $url;
  }

  /*
   * This function used to check weather column exist or not.
   */
  public function _columnExist($tableName, $columnName)
  {
    $searchCols = $this->getToDb()
        ->query
          ('SHOW COLUMNS FROM '
          . $this->getToDb()->quoteIdentifier($tableName)
          . " where field='" . $columnName . "'"
        )->fetch();
    if( $searchCols === false )
      return false;
    return true;
  }

  /*
   * CHECKING WHEATER PLUGIN EXIST OR NOT
   */
  protected function isPluginExist($name)
  {
    $pluginEnabled = $this->getToDb()->select()
      ->from('engine4_core_modules', 'enabled')
      ->where('name = ?', $name)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $pluginEnabled )
      return true;
    return false;
  }

  //GET COMMENT MAPPED DATA
  public function getCommentMap($name, $key)
  {

    if( isset(self::$_commentMap[$name][$key]) ) {
      return self::$_commentMap[$name][$key];
    } else {
      return false;
      //throw new Engine_Exception('No comment mapping detected');
    }
  }

  //MAP THE COMMENT
  public function setCommentMap($name, $key, $pageIdentity)
  {

    self::$_commentMap[$name][$key] = $pageIdentity;
  }

  // RETRIEV CUSTOM FIELD MAPPED DATA
  public function getCustomFieldMap($key)
  {
    $session = new Zend_Session_Namespace();
    if( isset($session->customField[$key]) ) {
      return $session->customField[$key];
    } else {
      return false;
    }
  }

  // RETRIEV ALL CUSTOM FIELD MAPPED DATA
  public function getAllCustomFieldMap()
  {
    $session = new Zend_Session_Namespace();
    return $session->customField;
  }

  // SET CUSTOM FIELD DATA
  public function setCustomFieldMap($key, $value)
  {
    $session = new Zend_Session_Namespace();
    $session->customField[$key] = $value;
  }

  // RETRIEV ALL CUSTOM FIELD MAPPED DATA
  public function inializeCustomFieldMap()
  {
    $session = new Zend_Session_Namespace();
    $session->customField = array();
  }

  protected function createFileDiffSize($file, $fileInfo, $fileSize = null)
  {
    if( empty($fileSize) ) {
      $prefix = $this->getfromPrefix();
      $picSize = $this->getFromDb()
        ->select()
        ->from($prefix . "setting", 'value_actual')
        ->where('var_name = ?', 'photo_pic_sizes')
        ->query()
        ->fetchColumn(0);
      $fileSize = 0;
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
              $fileSize = $dt[0];
          }
        }
      }
    }
    $output = explode(".", $file);
    $fname = $output[count($output) - 2];
    $output[count($output) - 2] = $fname . "_" . $fileSize;
    $file = implode(".", $output);
    try {
      $filephoto_id = $this->_translateFile($file, $fileInfo, true);
    } catch( Exception $ex ) {
      $filephoto_id = null;
      $this->_logFile($ex->getMessage());
    }
    return $filephoto_id;
  }
}
