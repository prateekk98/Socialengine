<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Core.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Plugin_Core extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $viewer = Engine_Api::_()->user()->getViewer();
        $params = $request->getParams();
        $site_password_reset = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.general.site.password.reset', 0);
        if(!empty($_SESSION['requirepassword'])){
            unset($_SESSION['requirepassword']);
        }
        if(!empty($params['action']) && $params['action'] != "require-password" && $params['action'] != "logout") {
            if ($viewer->getIdentity() && $viewer->level_id != 2  && $site_password_reset && (!$viewer->last_password_reset || strtotime($viewer->last_password_reset . ' + ' . $site_password_reset . " days") < time())) {
                $request->setModuleName('user');
                $request->setActionName('require-password');
                $request->setControllerName('settings');
                $_SESSION['requirepassword'] = 1;
            }
        }
    }

    public function onUserDeleteBefore($event)
    {
        $payload = $event->getPayload();
        if( $payload instanceof User_Model_User ) {

            // Remove from online users
            $onlineUsersTable = Engine_Api::_()->getDbtable('online', 'user');
            $onlineUsersTable->delete(array(
                'user_id = ?' => $payload->getIdentity(),
            ));

            // Remove friends
            $payload->membership()->removeAllUserFriendship();

            // Remove all cases user is in a friend list
            $payload->lists()->removeUserFromLists();

            // Remove all friend list created by the user
            $payload->lists()->removeUserLists();

            // Remove facebook/twitter associations
            $db = Engine_Db_Table::getDefaultAdapter();
            $db->delete('engine4_user_facebook', array(
                'user_id = ?' => $payload->getIdentity(),
            ));
            $db->delete('engine4_user_twitter', array(
                'user_id = ?' => $payload->getIdentity(),
            ));
        }
    }

    

public function onUserEnable($event)
{
    $payload = $event->getPayload();
    $user = $payload['user'];
    if( !($user instanceof User_Model_User) ) {
        return;
    }

    // update networks
    Engine_Api::_()->network()->recalculate($user);

    // Create activity for them if it doesn't exist
    $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
    $signupActionIdentity = $actionTable->select()
        ->from($actionTable, 'action_id')
        ->where('type = ?', 'signup')
        ->where('subject_type = ?', $user->getType())
        ->where('subject_id = ?', $user->getIdentity())
        ->query()
        ->fetchColumn();
    if( !$signupActionIdentity ) {
        $actionTable->addActivity($user, $user, 'signup');
    }

    // Note: this will get sent to users who are re-enabled after being disabled
    // by an admin
    if ( empty($payload['shouldSendWelcomeEmail']) && empty($payload['shouldSendApprovedEmail']) ) {
        return;
    }

    try {
        $emailOptions = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => time(),
            'recipient_title' => $user->getTitle(),
            'recipient_link' => $user->getHref(),
            'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
            'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        );
        if ( !empty($payload['shouldSendApprovedEmail']) ) {
            // Send a notification that the account was not approved previously
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user, 'user_account_approved', $emailOptions);
        }
        // Send welcome email?
        if( !empty($payload['shouldSendWelcomeEmail']) ) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($user,'core_welcome', $emailOptions);
        }
    } catch( Exception $e ) {}
}

public function getAdminNotifications($event)
{
    // Awaiting approval
    $userTable = Engine_Api::_()->getItemTable('user');
    $select = new Zend_Db_Select($userTable->getAdapter());
    $select->from($userTable->info('name'), 'COUNT(user_id) as count')
        ->where('enabled = ?', 0)
        ->where('approved = ?', 0)
    ;

    $data = $select->query()->fetch();
    if( empty($data['count']) ) {
        return;
    }

    $translate = Zend_Registry::get('Zend_Translate');
    $message = vsprintf($translate->translate(array(
        'There is <a href="%s">%d new member</a> awaiting your approval.',
        'There are <a href="%s">%d new members</a> awaiting your approval.',
        $data['count']
    )), array(
        Zend_Controller_Front::getInstance()->getRouter()->assemble(array('module' => 'user', 'controller' => 'manage'), 'admin_default', true) . '?enabled=0',
        $data['count'],
    ));

    $event->addResponse($message);
}

public function onUserCreateAfter($event)
{
    $payload = $event->getPayload();
    if( $payload instanceof User_Model_User ) {
//      if( 'none' != Engine_Api::_()->getApi('settings', 'core')->core_facebook_enable ){
//        $facebook = User_Model_DbTable_Facebook::getFBInstance();
//        if ($facebook->getUser()) {
//          try {
//            $facebook->api('/me');
//            $table = Engine_Api::_()->getDbtable('facebook', 'user');
//            $row = $table->fetchRow(array('user_id = ?'=>$payload->getIdentity()));
//            if (!$row) {
//              $row = Engine_Api::_()->getDbtable('facebook', 'user')->createRow();
//              $row->user_id = $payload->getIdentity();
//            }
//            $row->facebook_uid = $facebook->getUser();
//            $row->save();
//          } catch (Exception $e) {}
//        }
//      }

        // Set default email notifications
        $notificationTypesTable = Engine_Api::_()->getDbtable('notificationTypes', 'activity');

        // For backwards compatiblitiy this block will only execute if the
        // getDefaultNotifications function exists. If notifications aren't
        // being added to the engine4_activity_notificationsettings table
        // check to see if the Activity_Model_DbTable_NotificationTypes class
        // is out of date
        if( method_exists($notificationTypesTable, 'getDefaultNotifications') ){
            $defaultNotifications = $notificationTypesTable->getDefaultNotifications();

            Engine_Api::_()->getDbtable('notificationSettings', 'activity')
                ->setEnabledNotifications($payload, $defaultNotifications);
        }

        // Set default email
        $emailTypesTable = Engine_Api::_()->getDbtable('mailTemplates', 'core');
        if( method_exists($emailTypesTable, 'getDefaultEmails') ){
            $defaultNotifications = $emailTypesTable->getDefaultEmails();
            Engine_Api::_()->getDbtable('emailSettings', 'user')->setEnabledEmails($payload, $defaultNotifications);
        }
    }
}

public function onActivityActionUpdateAfter($event)
{
    $payload = $event->getPayload();
    $modifiedFields = $payload->getModifiedFieldsName();
    $oldData = $payload->getCleanData();
    $user = $payload->getOwner();

    if( $payload['type'] == 'status' && in_array('body', $modifiedFields) && $oldData['body'] === $user->status ) {
        $user->status = $payload['body'];
        $user->save();
    }
}
}
