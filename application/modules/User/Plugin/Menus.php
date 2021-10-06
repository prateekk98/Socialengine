<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Menus.php 9770 2012-08-30 02:36:05Z richard $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Plugin_Menus
{
  public function canDelete()
  {
    // Check subject
    if( !Engine_Api::_()->core()->hasSubject('user') ) {
      return false;
    }
    $subject = Engine_Api::_()->core()->getSubject('user');

    // Check viewer
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer || !$viewer->getIdentity() ) {
      return false;
    }

    // Check auth
    return (bool) $subject->authorization()->isAllowed($viewer, 'delete');
  }

  // core_main
  public function onMenuInitialize_CoreMainHome($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $request = Zend_Controller_Front::getInstance()->getRequest();
    $route = array(
      'route' => 'default',
    );

    if( $viewer->getIdentity() ) {
      $route['route'] = 'user_general';
      $route['params'] = array(
        'action' => 'home',
      );
      if( 'user' == $request->getModuleName() &&
        'index' == $request->getControllerName() &&
        'home' == $request->getActionName() ) {
        $route['active'] = true;
      }
    }
    if( !empty($row->params['icon']) ) {
      return array_merge($row->params, $route);
    }
    return $route;
  }

  // core_mini
  public function onMenuInitialize_CoreMiniAdmin($row)
  {
    if( Engine_Api::_()->user()->getViewer()->isAllowed('admin') ) {
      return array(
        'label' => $row->label,
        'route' => 'admin_default',
        'class' => 'no-dloader',
      );
    }

    return false;
  }

  public function onMenuInitialize_CoreMiniProfile($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      $photo = '';
      if( Zend_Registry::isRegistered('Zend_View') ) {
        $view = Zend_Registry::get('Zend_View');
        $photo = $view->itemPhoto($viewer, 'thumb.icon');
      }
      return array(
        'label' => $photo . '<span>'. $row->label . '</span>',
        'uri' => $viewer->getHref(),
      );
    }

    return false;
  }

  public function onMenuInitialize_CoreMiniSettings($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      return array(
        'label' => $row->label,
        'route' => 'user_extended',
        'params' => array(
          'controller' => 'settings',
          'action' => 'general',
        )
      );
    }

    return false;
  }

  public function onMenuInitialize_CoreMiniAuth($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      return array(
        'label' => 'Sign Out',
        'route' => 'user_logout',
        'class' => 'no-dloader',
      );
    } else {
      $request = Zend_Controller_Front::getInstance()->getRequest(); 
      $uri = $request->getParam('return_url');
      if(substr($uri, 0, 3) != '64-'){
        $uri = '64-' . base64_encode($_SERVER['REQUEST_URI']);
      }
      return array(
        'class' => 'user_auth_link',
        'label' => 'Sign In',
        'route' => 'user_login',
        'params' => array(
          // Nasty hack
          'return_url' => $uri,
        ),
      );
    }
  }

  public function onMenuInitialize_CoreMiniSignup($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( !$viewer->getIdentity() ) {
      return array(
        'label' => 'Sign Up',
        'route' => 'user_signup',
        'class' => 'user_signup_link',
      );
    }

    return false;
  }

  // user_edit
  public function onMenuInitialize_UserEditStyle($row)
  {
    if( Engine_Api::_()->core()->hasSubject('user') ) {
      $user = Engine_Api::_()->core()->getSubject('user');
    } else {
      $user = Engine_Api::_()->user()->getViewer();
    }
    if( !$user->getIdentity() ) {
      return false;
    }
    return (bool) Engine_Api::_()->getDbtable('permissions', 'authorization')
        ->getAllowed('user', $user->level_id, 'style');
  }

  // user_home
  public function onMenuInitialize_UserHomeView($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    if( $viewer->getIdentity() ) {
      return array(
        'label' => $row->label,
        'icon' => isset($row->params['icon']) ? $row->params['icon'] : '',
        'route' => 'user_profile',
        'params' => array(
          'id' => $viewer->getIdentity()
        )
      );
    }
    return false;
  }

  public function onMenuInitialize_UserHomeEdit($row)
  {
    return array(
      'label' => 'Edit My Profile',
      'class' => 'icon_edit',
      'route' => 'user_extended',
      'params' => array(
        'controller' => 'edit',
        'action' => 'profile'
      )
    );
  }
  public function onMenuInitialize_UserDeletePhotos($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();
    if( !$viewer->isSelf($subject) ) {
      return false;
    }

    if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')) {
      return false;
    }

    if( $subject->authorization()->isAllowed($viewer, 'edit') ) {
      return array(
        'label' => 'Delete Profile Photos',
        'route' => 'user_extended',
        'params' => array(
          'controller' => 'edit',
          'action' => 'profile-photos',
        )
      );
    }

    return false;
  }

  // user_profile
  public function onMenuInitialize_UserProfileEdit($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    $label = "Edit My Profile";
    if( !$viewer->isSelf($subject) ) {
      $label = "Edit Member Profile";
    }
    $auth = $subject->isSuperAdmin() ? $viewer->isSuperAdmin($subject) : 1;
    if( $subject->authorization()->isAllowed($viewer, 'edit') && $auth) {
      return array(
        'label' => $label,
        'class' => 'icon_edit',
        'route' => 'user_extended',
        'params' => array(
          'controller' => 'edit',
          'action' => 'profile',
          'id' => ( $viewer->getGuid(false) == $subject->getGuid(false) ? null : $subject->getIdentity() ),
        )
      );
    }

    return false;
  }

  public function onMenuInitialize_UserProfileFriend($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid(false) === $subject->getGuid(false) ) {
      return false;
    }

    // No blocked
    if( $viewer->isBlockedBy($subject) ) {
      return false;
    }

    // Check if friendship is allowed in the network
    $eligible = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.eligible', 2);
    if( !$eligible ) {
      return '';
    }

    // check admin level setting if you can befriend people in your network
    else if( $eligible == 1 ) {

      $networkMembershipTable = Engine_Api::_()->getDbtable('membership', 'network');
      $networkMembershipName = $networkMembershipTable->info('name');

      $select = new Zend_Db_Select($networkMembershipTable->getAdapter());
      $select
        ->from($networkMembershipName, 'user_id')
        ->join($networkMembershipName, "`{$networkMembershipName}`.`resource_id`=`{$networkMembershipName}_2`.resource_id", null)
        ->where("`{$networkMembershipName}`.user_id = ?", $viewer->getIdentity())
        ->where("`{$networkMembershipName}_2`.user_id = ?", $subject->getIdentity())
      ;

      $data = $select->query()->fetch();

      if( empty($data) ) {
        return '';
      }
    }

    // One-way mode
    $direction = (int) Engine_Api::_()->getApi('settings', 'core')->getSetting('user.friends.direction', 1);
    if( !$direction ) {
      $viewerRow = $viewer->membership()->getRow($subject);
      $subjectRow = $subject->membership()->getRow($viewer);
      $params = array();

      // Viewer?
      if( null === $subjectRow ) {
        // Follow
        $params[] = array(
          'label' => 'Follow',
          'class' => 'smoothbox icon_friend_add',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'add',
            'user_id' => $subject->getIdentity()
          ),
        );
      } else if( $subjectRow->resource_approved == 0 ) {
        // Cancel follow request
        $params[] = array(
          'label' => 'Cancel Follow Request',
          'class' => 'smoothbox icon_friend_remove',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'cancel',
            'user_id' => $subject->getIdentity()
          ),
        );
      } else {
        // Unfollow
        $params[] = array(
          'label' => 'Unfollow',
          'class' => 'smoothbox icon_friend_remove',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'remove',
            'user_id' => $subject->getIdentity()
          ),
        );
      }
      // Subject?
      if( null === $viewerRow ) {
        // Do nothing
      } else if( $viewerRow->resource_approved == 0 ) {
        // Approve follow request
        $params[] = array(
          'label' => 'Approve Follow Request',
          'class' => 'smoothbox icon_friend_add',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'confirm',
            'user_id' => $subject->getIdentity()
          ),
        );
      } else {
        // Remove as follower?
        $params[] = array(
          'label' => 'Remove as Follower',
          'class' => 'smoothbox icon_friend_remove',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'remove',
            'user_id' => $subject->getIdentity(),
            'rev' => true,
          ),
        );
      }
      if( count($params) == 1 ) {
        return $params[0];
      } else if( count($params) == 0 ) {
        return false;
      } else {
        return $params;
      }
    }

    // Two-way mode
    else {
      $row = $viewer->membership()->getRow($subject);
      if( null === $row ) {
        // Add
        return array(
          'label' => 'Add to My Friends',
          'class' => 'smoothbox icon_friend_add',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'add',
            'user_id' => $subject->getIdentity()
          ),
        );
      } else if( $row->user_approved == 0 ) {
        // Cancel request
        return array(
          'label' => 'Cancel Friend Request',
          'class' => 'smoothbox icon_friend_remove',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'cancel',
            'user_id' => $subject->getIdentity()
          ),
        );
      } else if( $row->resource_approved == 0 ) {
        // Approve request
        return array(
          'label' => 'Approve Friend Request',
          'class' => 'smoothbox icon_friend_add',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'confirm',
            'user_id' => $subject->getIdentity()
          ),
        );
      } else {
        // Remove friend
        return array(
          'label' => 'Remove from Friends',
          'class' => 'smoothbox icon_friend_remove',
          'route' => 'user_extended',
          'params' => array(
            'controller' => 'friends',
            'action' => 'remove',
            'user_id' => $subject->getIdentity()
          ),
        );
      }
    }
  }

  public function onMenuInitialize_UserProfileBlock($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    // Can't block self or if not logged in
    if( !$viewer->getIdentity() || $viewer->getGuid() == $subject->getGuid() ) {
      return false;
    }

    if( !$viewer->isAllowed('user', 'block') ) {
      return false;
    }

    if( !$subject->isBlockedBy($viewer) ) {
      return array(
        'label' => 'Block Member',
        'class' => 'smoothbox icon_block',
        'route' => 'user_extended',
        'params' => array(
          'controller' => 'block',
          'action' => 'add',
          'user_id' => $subject->getIdentity()
        ),
      );
    } else {
      return array(
        'label' => 'Unblock Member',
        'class' => 'smoothbox icon_block',
        'route' => 'user_extended',
        'params' => array(
          'controller' => 'block',
          'action' => 'remove',
          'user_id' => $subject->getIdentity()
        ),
      );
    }
  }

  public function onMenuInitialize_UserProfileReport($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if( !$viewer->getIdentity() ||
      !$subject->getIdentity() ||
      $viewer->isSelf($subject) ) {
      return false;
    } else {
      return array(
        'label' => 'Report',
        'class' => 'smoothbox icon_report',
        'route' => 'default',
        'params' => array(
          'module' => 'core',
          'controller' => 'report',
          'action' => 'create',
          'subject' => $subject->getGuid(),
          'format' => 'smoothbox',
        ),
      );
    }
  }

  public function onMenuInitialize_UserProfileAdmin($row)
  {
    $viewer = Engine_Api::_()->user()->getViewer();
    $subject = Engine_Api::_()->core()->getSubject();

    if( !$subject->authorization()->isAllowed($viewer, 'delete') ) {
      return false;
    }

    $auth = $subject->isSuperAdmin() ? $viewer->isSuperAdmin($subject) : 1;
    if( !$viewer->isAllowed('admin') || !$viewer->isAdmin() || !$auth) {
      return false;
    }
    return array(
      'label' => 'Admin Settings',
      'class' => 'smoothbox icon_edit',
      'route' => 'admin_default',
      'params' => array(
        'module' => 'user',
        'controller' => 'manage',
        'action' => 'edit',
        'id' => $subject->getIdentity(),
        'format' => 'smoothbox',
      ),
    );
  }
}
