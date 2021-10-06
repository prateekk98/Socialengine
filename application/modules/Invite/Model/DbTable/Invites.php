<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Invites.php 10180 2014-04-28 21:02:01Z lucas $
 * @author     Steve
 */

/**
 * @category   Application_Extensions
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Invite_Model_DbTable_Invites extends Engine_Db_Table
{
  protected $_name = 'invites';
  protected $_alreadyMembers = [];
  public function sendInvites(User_Model_User $user, $recipients, $message, $friendship, $resend = '')
  {
    $settings = Engine_Api::_()->getApi('settings', 'core');
    
    // Check recipients
    if( is_string($recipients) ) {
      $recipients = preg_split("/[\s,]+/", $recipients);
    }
    if( is_array($recipients) ) {
      $recipients = array_map('strtolower', array_unique(array_filter(array_map('trim', $recipients))));
    }
    if( !is_array($recipients) || empty($recipients) ) {
      return 0;
    }
    
    // Only allow a certain number for now
    $max = $settings->getSetting('invite.max', 10);
    if( count($recipients) > $max ) {
      $recipients = array_slice($recipients, 0, $max);
    }

    // Check message
    $message = trim($message);
    
    // Get tables
    $userTable = Engine_Api::_()->getItemTable('user');
    $inviteTable = $this;
    $inviteOnlySetting = $settings->getSetting('user.signup.inviteonly', 0);

    // Get ones that are already members
    $alreadyMembers = $userTable->fetchAll(array('email IN(?)' => $recipients));
    $alreadyMemberEmails = array();
    foreach( $alreadyMembers as $alreadyMember ) {
      if( in_array(strtolower($alreadyMember->email), $recipients) ) {
        $alreadyMemberEmails[] = strtolower($alreadyMember->email);
        $this->_alreadyMembers[] = $alreadyMember;
      }
    }
    // Remove the ones that are already members
    $recipients = array_diff($recipients, $alreadyMemberEmails);
    $emailsSent = 0;

    // Send them invites
    foreach( $recipients as $recipient ) {
      if(empty($resend) && $this->isInvited(array('email'=>$recipient))){
        continue;
      }
      // start inserting database entry
      // generate unique invite code and confirm it truly is unique
      do {
        $inviteCode = substr(md5(rand(0, 999) . $recipient), 10, 7);
      } while( null !== $inviteTable->fetchRow(array('code = ?' => $inviteCode)) );

      $row = $inviteTable->createRow();
      $row->user_id = $user->getIdentity();
      $row->recipient = $recipient;
      $row->send_request = $friendship;
      $row->code = $inviteCode;
      $row->timestamp = new Zend_Db_Expr('NOW()');
      $row->message = $message;
      $row->save();
      
      try {
        
        $inviteUrl = Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
              'module' => 'invite',
              'controller' => 'signup',
            ), 'default', true)
          . '?'
          . http_build_query(array('code' => $inviteCode, 'email' => $recipient))
          ;

        $message = str_replace('%invite_url%', $inviteUrl, $message);
        
        // Send mail
        $mailType = ( $inviteOnlySetting == 2 ? 'invite_code' : 'invite' );
        $mailParams = array(
          'host' => $_SERVER['HTTP_HOST'],
          'email' => $recipient,
          'date' => time(),
          'sender_email' => $user->email,
          'sender_title' => $user->getTitle(),
          'sender_link' => $user->getHref(),
          'sender_photo' => $user->getPhotoUrl('thumb.icon'),
          'message' => $message,
          'object_link' => $inviteUrl,
          'code' => $inviteCode,
        );

        Engine_Api::_()->getApi('mail', 'core')->sendSystem(
          $recipient,
          $mailType,
          $mailParams
        );
        
      } catch( Exception $e ) {
        // Silence
        if( APPLICATION_ENV == 'development' ) {
          throw $e;
        }
        continue;
      }
      
      $emailsSent++;
    }

    $user->invites_used += $emailsSent;
    $user->save();

    // @todo Send requests to users that are already members?

    return $emailsSent;
  }
  public function getAlreadyMembers(){
    return $this->_alreadyMembers;
  }
  public function setDefaultAlreadyMembers($default = array()){
    $this->_alreadyMembers = $default;
  }
  public function isInvited($subject){
    $recipient = $subject['email'];
    $select = $this->select()->from($this->info('name'), 'user_id');
    $select->where('recipient = ?', $recipient);
    return $this->fetchRow($select);
  }
  
  public function getAllInvites($recipients) {
    $viewer = Engine_Api::_()->user()->getViewer();
    $recipient = explode(',',$recipients);
    $select = $this->select()->from($this->info('name'));
    $select->where('recipient IN (?)', array_map('trim', $recipient));
          //->where('user_id <> ?', $viewer->getIdentity());
    return $this->fetchAll($select);
  }
  
  public function canInvite($email){
    return $this->select()
                ->from($this->info('name'), 'id')
                ->where('recipient = ?', $email)
                ->query()
                ->fetchColumn();
  }
}
