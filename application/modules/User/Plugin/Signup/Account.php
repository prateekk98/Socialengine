<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Account.php 10099 2013-10-19 14:58:40Z ivan $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Plugin_Signup_Account extends Core_Plugin_FormSequence_Abstract
{
  protected $_name = 'account';
  protected $_formClass = 'User_Form_Signup_Account';
  protected $_script = array('signup/form/account.tpl', 'user');
  protected $_adminFormClass = 'User_Form_Admin_Signup_Account';
  protected $_adminScript = array('admin-signup/account.tpl', 'user');
  public $email = null;

  public function onView()
  {
    if (!empty($_SESSION['facebook_signup']) ||
      !empty($_SESSION['twitter_signup'])) {

      // Attempt to preload information
      if (!empty($_SESSION['facebook_signup'])) {
        try {
          $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
          $facebook = $facebookTable->getApi();
          $settings = Engine_Api::_()->getDbtable('settings', 'core');
          if ($facebook && $settings->core_facebook_enable) {
            // Get email address
            $apiInfo = $facebook->api('/me?fields=name,gender,email,locale');
            // General
            $form = $this->getForm();

            if (($emailEl = $form->getElement('email')) && !$emailEl->getValue()) {
              $emailEl->setValue($apiInfo['email']);
            }
            if (($usernameEl = $form->getElement('username')) && !$usernameEl->getValue()) {
              $usernameEl->setValue(preg_replace('/[^A-Za-z]/', '', $apiInfo['name']));
            }

            // Locale
            $localeObject = new Zend_Locale($apiInfo['locale']);
            if (($localeEl = $form->getElement('locale')) && !$localeEl->getValue()) {
              $localeEl->setValue($localeObject->toString());
            }
            if (($languageEl = $form->getElement('language')) && !$languageEl->getValue()) {
              if (isset($languageEl->options[$localeObject->toString()])) {
                $languageEl->setValue($localeObject->toString());
              } else if (isset($languageEl->options[$localeObject->getLanguage()])) {
                $languageEl->setValue($localeObject->getLanguage());
              }
            }
          }
        } catch (Exception $e) {
          // Silence?
        }
      }

      // Attempt to preload information
      if (!empty($_SESSION['twitter_signup'])) {
        try {
          $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
          $twitter = $twitterTable->getApi();
          $settings = Engine_Api::_()->getDbtable('settings', 'core');
          if ($twitter && $settings->core_twitter_enable) {
            $accountInfo = $twitter->account->verify_credentials();

            // General
            $this->getForm()->populate(array(
              //'email' => $apiInfo['email'],
              'username' => preg_replace('/[^A-Za-z]/', '', $accountInfo->name), // $accountInfo->screen_name
              // 'timezone' => $accountInfo->utc_offset, (doesn't work)
              'language' => $accountInfo->lang,
            ));
          }
        } catch (Exception $e) {
          // Silence?
        }
      }
    }

    if (isset($_SESSION['Payment_Plugin_Signup_Subscription'])) {
      try {
        $packageId = $_SESSION['Payment_Plugin_Signup_Subscription']['data']['package_id'];
        $package = Engine_Api::_()->getItem('payment_package', $packageId);
        if (empty($package)) {
          return;
        }

        $profileTypeIds = Engine_Api::_()->getDbtable('mapProfileTypeLevels', 'authorization')
          ->getMappedProfileTypeIds($package->level_id);
        if (empty($profileTypeIds)) {
          return;
        }

        $form = $this->getForm();
        if (count($profileTypeIds) == 1) {
          $form->removeElement('profile_type');
          // Hidden Profile Types
          $form->addElement('Hidden', 'profile_type', array(
            'value' => $profileTypeIds[0]['profile_type_id']
          ));
          return;
        }

        $profileTypes = Engine_Api::_()->getDbtable('options', 'authorization')->getAllProfileTypes();
        $profileTypeOptions = array('' => '');
        foreach ($profileTypes as $profileType) {
          $showOption  = false;
          foreach($profileTypeIds as $profileTypeId) {
            if ($profileType->option_id === $profileTypeId['profile_type_id']) {
              $showOption = true;
            }
          }
          if ($showOption) {
            $profileTypeOptions[$profileType->option_id] = $profileType->label;
          }
        }
        $form->getElement('profile_type')->setMultiOptions($profileTypeOptions);
      } catch (Exception $ex) {
          // Silence?
      }
    }
  }
  
  public function onSubmit(Zend_Controller_Request_Abstract $request) {
    // Form was not valid
    if(!$this->getForm()->isValid($request->getPost()) ) {
      $this->getSession()->active = true;
      $this->onSubmitNotIsValid();
      return false;
    }
    //verification code mail send
    $stepTable = Engine_Api::_()->getDbTable('signup', 'user');
    $stepRow = $stepTable->fetchRow($stepTable->select()->where('class = ?', 'User_Plugin_Signup_Otp'));
    if($stepRow->enable) {
      $email = $_POST[$this->getForm()->getEmailElementFieldName()];
      $codeTable = Engine_Api::_()->getDbTable('codes', 'user');
      $isEmailExist = $codeTable->isEmailExist($email);
      if($isEmailExist) {
        $isEmailExist->delete();
      }
      $code = rand(100000, 999999);
      $row = $codeTable->createRow();
      $row->email = $email;
      $row->code = $code;
      $row->creation_date = date('Y-m-d H:i:s');
      $row->modified_date = date('Y-m-d H:i:s');
      $row->save();
      Engine_Api::_()->getApi('mail', 'core')->sendSystem($email, 'user_otp', array('host' => $_SERVER['HTTP_HOST'], 'code' => $code));
    }
    parent::onSubmit($request);
  }

  public function onProcess()
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $random = ($settings->getSetting('user.signup.random', 0) == 1);
    $emailadmin = ($settings->getSetting('user.signup.adminemail', 0) == 1);
    if ($emailadmin) {
      // the signup notification is emailed to the first SuperAdmin by default
      $users_table = Engine_Api::_()->getDbtable('users', 'user');
      $users_select = $users_table->select()
        ->where('level_id = ?', 1)
        ->where('enabled >= ?', 1);
      $super_admin = $users_table->fetchRow($users_select);
    }
    $data = $this->getSession()->data;

    // Add email and code to invite session if available
    $inviteSession = new Zend_Session_Namespace('invite');
    if (isset($data['email'])) {
      $inviteSession->signup_email = $data['email'];
    }
    if (isset($data['code'])) {
      $inviteSession->signup_code = $data['code'];
    }

    if ($random) {
      $data['password'] = Engine_Api::_()->user()->randomPass(10);
    }

    if (!empty($data['language'])) {
      $data['locale'] = $data['language'];
    }

    // Create user
    // Note: you must assign this to the registry before calling save or it
    // will not be available to the plugin in the hook
    $this->_registry->user = $user = Engine_Api::_()->getDbtable('users', 'user')->createRow();
    $user->setFromArray($data);
    $user->save();

    Engine_Api::_()->user()->setViewer($user);

    // Increment signup counter
    Engine_Api::_()->getDbtable('statistics', 'core')->increment('user.creations');

    if ($user->verified && $user->enabled) {
      // Create activity for them
      Engine_Api::_()->getDbtable('actions', 'activity')->addActivity($user, $user, 'signup');
      // Set user as logged in if not have to verify email
      Engine_Api::_()->user()->getAuth()->getStorage()->write($user->getIdentity());
    }

    $mailType = null;
    $mailParams = array(
      'host' => $_SERVER['HTTP_HOST'],
      'email' => $user->email,
      'date' => time(),
      'recipient_title' => $user->getTitle(),
      'recipient_link' => $user->getHref(),
      'recipient_photo' => $user->getPhotoUrl('thumb.icon'),
      'object_link' => Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
    );

    // Add password to email if necessary
    if ($random) {
      $mailParams['password'] = $data['password'];
    }

    // Mail stuff
    switch ($settings->getSetting('user.signup.verifyemail', 0)) {
      case 0:
        // only override admin setting if random passwords are being created
        if ($random) {
          $mailType = 'core_welcome_password';
        }
        if ($emailadmin) {
          $mailAdminType = 'notify_admin_user_signup';
          $siteTimezone = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.timezone', 'America/Los_Angeles');
          $date = new DateTime("now", new DateTimeZone($siteTimezone));
          $mailAdminParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => $date->format('F j, Y, g:i a'),
            'recipient_title' => $super_admin->displayname,
            'object_title' => $user->displayname,
            'object_link' => $user->getHref(),
          );
        }
        break;

      case 1:
        // send welcome email
        $mailType = ($random ? 'core_welcome_password' : 'core_welcome');
        if ($emailadmin) {
          $mailAdminType = 'notify_admin_user_signup';
          $siteTimezone = Engine_Api::_()->getApi('settings', 'core')->getSetting('core.locale.timezone', 'America/Los_Angeles');
          $date = new DateTime("now", new DateTimeZone($siteTimezone));
          $mailAdminParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => $date->format('F j, Y, g:i a'),
            'recipient_title' => $super_admin->displayname,
            'object_title' => $user->getTitle(),
            'object_link' => $user->getHref(),
          );
        }
        break;

      case 2:
      case 3:
        // verify email before enabling account
        $verify_table = Engine_Api::_()->getDbtable('verify', 'user');
        $verify_row = $verify_table->createRow();
        $verify_row->user_id = $user->getIdentity();
        $verify_row->code = md5($user->email
          . $user->creation_date
          . $settings->getSetting('core.secret', 'staticSalt')
          . (string) rand(1000000, 9999999));
        $verify_row->date = $user->creation_date;
        $verify_row->save();

        $mailType = ($random ? 'core_verification_password' : 'core_verification');

        $mailParams['object_link'] = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'action' => 'verify',
          'token' => Engine_Api::_()->user()->getVerifyToken($user->getIdentity()),
          'verify' => $verify_row->code
          ), 'user_signup', true);

        if ($emailadmin) {
          $mailAdminType = 'notify_admin_user_signup';

          $mailAdminParams = array(
            'host' => $_SERVER['HTTP_HOST'],
            'email' => $user->email,
            'date' => date("F j, Y, g:i a"),
            'recipient_title' => $super_admin->displayname,
            'object_title' => $user->getTitle(),
            'object_link' => $user->getHref(),
          );
        }
        break;

      default:
        // do nothing
        break;
    }

    if (!empty($mailType)) {
      $this->_registry->mailParams = $mailParams;
      $this->_registry->mailType = $mailType;
      // Moved to User_Plugin_Signup_Fields
      // Engine_Api::_()->getApi('mail', 'core')->sendSystem(
      //   $user,
      //   $mailType,
      //   $mailParams
      // );
    }

    if (!empty($mailAdminType)) {
      $this->_registry->mailAdminParams = $mailAdminParams;
      $this->_registry->mailAdminType = $mailAdminType;
      // Moved to User_Plugin_Signup_Fields
      // Engine_Api::_()->getApi('mail', 'core')->sendSystem(
      //   $user,
      //   $mailType,
      //   $mailParams
      // );
    }

    // Attempt to connect facebook
    if (!empty($_SESSION['facebook_signup'])) {
      try {
        $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
        $facebook = $facebookTable->getApi();
        $settings = Engine_Api::_()->getDbtable('settings', 'core');
        if ($facebook && $settings->core_facebook_enable) {
          $facebookTable->insert(array(
            'user_id' => $user->getIdentity(),
            'facebook_uid' => $facebook->getUser(),
            'access_token' => $facebook->getAccessToken(),
            //'code' => $code,
            'expires' => 0, // @todo make sure this is correct
          ));
        }
      } catch (Exception $e) {
        // Silence
        if ('development' == APPLICATION_ENV) {
          echo $e;
        }
      }
    }

    // Attempt to connect twitter
    if (!empty($_SESSION['twitter_signup'])) {
      try {
        $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
        $twitter = $twitterTable->getApi();
        $twitterOauth = $twitterTable->getOauth();
        $settings = Engine_Api::_()->getDbtable('settings', 'core');
        if ($twitter && $twitterOauth && $settings->core_twitter_enable) {
          $accountInfo = $twitter->account->verify_credentials();
          $twitterTable->insert(array(
            'user_id' => $user->getIdentity(),
            'twitter_uid' => $accountInfo->id,
            'twitter_token' => $twitterOauth->getToken(),
            'twitter_secret' => $twitterOauth->getTokenSecret(),
          ));
        }
      } catch (Exception $e) {
        // Silence?
        if ('development' == APPLICATION_ENV) {
          echo $e;
        }
      }
    }

  }

  public function onAdminProcess($form)
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    $values = $form->getValues();
    $settings->user_signup = $values;
    if ($values['inviteonly'] == 1) {
      $step_table = Engine_Api::_()->getDbtable('signup', 'user');
      $step_row = $step_table->fetchRow($step_table->select()->where('class = ?', 'User_Plugin_Signup_Invite'));
      $step_row->enable = 0;
    }

    $form->addNotice('Your changes have been saved.');
  }
}
