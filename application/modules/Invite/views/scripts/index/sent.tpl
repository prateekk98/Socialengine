<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Invite
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: sent.tpl 10180 2014-04-28 21:02:01Z lucas $
 * @author     Steve
 */
?>
<?php $recipients = explode(',', $this->recipients); ?>
<?php $recipients = array_map('trim', $recipients); ?>
<div class="invites_notify">
<?php $allInvites = $this->allInvites; //Engine_Api::_()->getDbTable('invites', 'invite')->getAllInvites($this->recipients); ?>
<?php $excludeInvite = array(); ?>
<?php foreach($allInvites as $recipientInvite) { ?>
  <?php $excludeInvite[] = $recipientInvite->recipient; ?>
<?php } ?>
<?php $invited = implode(', ',array_diff($recipients, $excludeInvite)); ?>
<?php if(count($recipients) > 1) { ?>
  <h2><?php echo $this->translate(array("Invitation Sent","Invitations Sent",$this->emails_sent)) ?></h2>
  <?php if($invited) { ?>
    <?php echo $this->translate('<b>%s</b> has been successfully invited to join your social network.', $invited); ?>
  <?php } ?>
  <?php if(count($allInvites)) { ?>
  <p><?php echo $this->translate("Below listed Email Addresses has already been invited to join network."); ?><p>
  <?php } ?>
  <div class="invite_table">
  <?php foreach($allInvites as $invite) { ?>
   <div>
    <div class="invite_email"><?php echo $invite->recipient; ?></div>
    <div id="notifyadmin_button_<?php echo $invite->id; ?>">
      <?php if($this->viewer->getIdentity() == $invite->user_id) { ?>
        <a type="button" href="javascript:void(0);" onclick="resendInvite('<?php echo $invite->id; ?>');" class="invite-secondary"><i class="far fa-envelope"></i><?php echo $this->translate("Resend Invite"); ?></a>
      <?php } else { ?>
        <a type="button" id="notifyadmin_button_<?php echo $invite->id; ?>" href="javascript:void(0);" onclick="notifyadmin('<?php echo $invite->id; ?>');" class="invite-secondary"><i class="far fa-user"></i><?php echo $this->translate("Notify Admin"); ?></a>
      <?php } ?>
    </div>
  </div>
  <?php } ?>
  </div>
<?php } else if(count($recipients) == 1) { ?>
  <?php if(empty($this->canInvite)) { ?>
    <?php $canInvite = Engine_Api::_()->getDbTable('invites', 'invite')->canInvite($this->recipients); ?>
    <?php $invite = Engine_Api::_()->getItem('invite', $canInvite); ?>
    <h2><?php echo $this->translate(array("Invitation Sent","Invitations Sent",$this->emails_sent)) ?></h2>
    <?php if($this->viewer->getIdentity() == $invite->user_id) { ?>
      <?php echo $this->translate('<b>%s</b> has been successfully invited to join your social network.', $this->recipients); ?>
<!--      <div id="notifyadmin_button_<?php //echo $invite->id; ?>">
        <a type="button" href="javascript:void(0);" onclick="resendInvite('<?php //echo $invite->id; ?>');" class="invite-primary"><i class="far fa-envelope"></i><?php //echo $this->translate("Resend Invite"); ?></a>
      </div>-->
    <?php } ?>
  <?php } else { ?>
    <?php $canInvite = Engine_Api::_()->getDbTable('invites', 'invite')->canInvite($this->recipients); ?>
    <?php if(!empty($canInvite)) { ?>
      <?php $invite = Engine_Api::_()->getItem('invite', $canInvite); ?>
      <h2><?php echo $this->translate(array("Invitation Not Sent","Invitations Not Sent",$this->emails_sent)) ?></h2>
      <?php if(empty($this->already_members) && $this->viewer->getIdentity() == $invite->user_id) { ?>
        <?php echo $this->translate('You have already invited <b>%s</b> to join the social network.', $this->recipients); ?>
        <div id="notifyadmin_button_<?php echo $invite->id; ?>">
          <a type="button" href="javascript:void(0);" onclick="resendInvite('<?php echo $invite->id; ?>');" class="invite-primary"><i class="far fa-envelope"></i><?php echo $this->translate("Resend Invite"); ?></a>
        </div>
      <?php } else { ?>
        <?php if(empty($this->already_members)) { ?>
        <?php echo $this->translate('<b>%s</b> has already been invited to join the social network.', $this->recipients); ?>
        <div id="notifyadmin_button_<?php echo $invite->id; ?>">
          <a type="button" id="notifyadmin_button_<?php echo $invite->id; ?>" href="javascript:void(0);" onclick="notifyadmin('<?php echo $invite->id; ?>');"  class="invite-primary"><i class="far fa-user"></i><?php echo $this->translate("Notify Admin"); ?></a>
        </div>
        <?php } ?>
      <?php } ?>
    <?php } ?>
  <?php } ?>
<?php } ?>
<p>
  <?php if ($this->form->friendship->getValue() == 1 ): ?>
  <?php echo $this->translate(array('If the person you invited decide to join, he/she will automatically receive a friend request from you.',
                                    'If the persons you invited decide to join, they will automatically receive a friend request from you.',
                                    $this->emails_sent)) ?>
  <?php endif ?>
</p>

<?php if (!empty($this->form->invalid_emails)): ?>
  <p><?php echo $this->translate('Invites were not sent to these email addresses because they do not appear to be valid:') ?></p>
  <ul>
    <?php foreach ($this->form->invalid_emails as $email): ?>
    <li><?php echo $email ?></li>
    <?php endforeach ?>
  </ul>
<?php endif ?>


<?php if (!empty($this->already_members)): ?>
  <p>

    <?php echo $this->translate(array('The member you are trying to invite is already a member: ','The following members you are trying to invite are already members: ',count($this->already_members))); ?>

    <?php $counter = 0; ?>
    <?php foreach ($this->already_members as $user): ?>
      <?php echo ($counter ? ', ' : '').$user->toString(); $counter++; ?>
    <?php endforeach ?>
  </p>
<?php endif ?>

<br />

<?php //echo $this->htmlLink(array('route'=>'default'), $this->translate('OK, thanks!'), array('class'=>'buttonlink icon_back')) ?>
</div>
<script>
  function notifyadmin(invite_id) {
    scriptJquery('#loading_image').show();
    (scriptJquery.ajax({
      url: en4.core.baseUrl + 'invite/index/notifyadmin',
      method: 'get',
      data: {
        'is_ajax': 1,
        'format': 'json',
        'invite_id': invite_id,
      },
      success: function(responseJSON) {
        var responseJSON = JSON.parse(responseJSON);
        if (responseJSON.status == 'true') {
          scriptJquery('#notifyadmin_button_'+invite_id).html('<a href="javascript:void(0);" type="button" class="button_alt disable invite-primary"><i class="far fa-user"></i>Notified Admin</a>');
        }
      }
    }));
  }
  
  function resendInvite(invite_id) {
    scriptJquery('#loading_image').show();
    (scriptJquery.ajax({
      url: en4.core.baseUrl + 'invite/index/resendinvite',
      method: 'get',
      data: {
        'is_ajax': 1,
        'format': 'json',
        'invite_id': invite_id,
      },
      success: function(responseJSON) {
        var responseJSON = JSON.parse(responseJSON);
        if (responseJSON.status == 'true') {
          scriptJquery('#notifyadmin_button_'+invite_id).html('<a href="javascript:void(0);" type="button" class="invite-primary disable"><i class="far fa-envelope"></i>Invited</a>');
        }
      }
    }));
  }
</script>
