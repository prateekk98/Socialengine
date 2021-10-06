<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Widget_ProfileInfoController extends Engine_Content_Widget_Abstract
{
  public function indexAction()
  {
    // Don't render this if not authorized
    $this->view->viewer = $viewer = Engine_Api::_()->user()->getViewer();
    if (!Engine_Api::_()->core()->hasSubject()) {
      return $this->setNoRender();
    }

    // Get subject and check auth
    $this->view->subject = $subject = Engine_Api::_()->core()->getSubject('user');
    if (!$subject->authorization()->isAllowed($viewer, 'view')) {
      return $this->setNoRender();
    }

    // Member type
    $subject = Engine_Api::_()->core()->getSubject();
    $fieldsByAlias = Engine_Api::_()->fields()->getFieldsObjectsByAlias($subject);

    if (!empty($fieldsByAlias['profile_type'])) {
      $optionId = $fieldsByAlias['profile_type']->getValue($subject);
      if ($optionId) {
        $optionObj = Engine_Api::_()->fields()
          ->getFieldsOptions($subject)
          ->getRowMatching('option_id', $optionId->value);
        if ($optionObj) {
          $this->view->memberType = $optionObj->label;
        }
      }
    }

    $widgetSettings = array("lastLoginDate", "lastUpdateDate", "inviteeName", "profileType", "memberLevel", "profileViews", "joinedDate", "friendsCount");
    
    $showWidgetSettings = array("showLastLogin", "showLastUpdate", "showInvitee", "showProfileType", "showMemberLevel", "showProfileViews", "showJoinedDate", "showFriendsCount");
    
    $isAdminAllow = array("lastLoginShow", "lastUpdateShow", "inviteeShow", "profileTypeShow", "memberLevelShow", "profileViewsShow", "joinedDateShow", "friendsCountShow");
    $isAtleastOne = false;

    foreach ($widgetSettings as $key => $value) {
      //if(Engine_Api::_()->authorization()->getPermission($viewer,'user', $isAdminAllow[$key])) {
        $userSetting = $subject->toArray();
        if (($subject->authorization()->isAllowed($viewer, $value) || $viewer->isAdmin() || (array_key_exists($value, $userSetting) && $userSetting[$value] == "everyone")) && ($subject->isAllowed('user', $isAdminAllow[$key]) || $viewer->isAdmin())) {
          $this->view->{$value} = true;
          $isAtleastOne = true;
        } 
//       } else if((Engine_Api::_()->authorization()->getPermission($viewer,'user', $showWidgetSettings[$key]) || $viewer->isAdmin())) {
//         $this->view->{$value} = true;
//         $isAtleastOne = true;
//       }
    }

    if (empty($isAtleastOne))
      return $this->setNoRender();
    
    // Networks
    $select = Engine_Api::_()->getDbtable('membership', 'network')->getMembershipsOfSelect($subject)
      ->where('hide = ?', 0);
    $this->view->networks = Engine_Api::_()->getDbtable('networks', 'network')->fetchAll($select);

    // Friend count
    $this->view->friendCount = $subject->membership()->getMemberCount($subject);

    // Inviter
    $inviter = Engine_Api::_()->getDbtable('invites', 'invite')->isInvited($subject);
    if(!empty($inviter->user_id)){
      $user = Engine_Api::_()->getItem('user', $inviter->user_id);
      if(isset($user->email))
        $this->view->inviter = $user->displayname;
    }
  }
}
