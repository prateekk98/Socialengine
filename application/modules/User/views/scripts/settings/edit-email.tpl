<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: delete.tpl 10003 2013-03-26 22:48:26Z john $
 * @author     Steve
 */
?>
<?php echo $this->form->render($this); ?>
<script type="text/javascript">
  scriptJquery(document).ready(function() {
    <?php if(isset($_POST['submit_code'])) { ?>
      scriptJquery('#code-wrapper').show();
      scriptJquery('#code-label').children('label').removeClass('optional').addClass('requried');
      scriptJquery('#verification_message-wrapper').show();
      scriptJquery('#verificationmessage').html('<?php echo $this->translate("We\'ve sent a verification code in an email to %s. Enter the verification code to proceed further.", $_POST['email']); ?>');
    <?php } else if(isset($_POST['submit'])) { ?>
      scriptJquery('#code-wrapper').show();
      scriptJquery('#code-label').children('label').removeClass('optional').addClass('requried');
      scriptJquery('#verification_message-wrapper').show();
      scriptJquery('#verificationmessage').html('<?php echo $this->translate("We've sent a verification code in an email to %s. Enter the verification code to proceed further.", $_POST['email']); ?>');
    <?php } else { ?>
      scriptJquery('#code-wrapper').hide();
      scriptJquery('#verification_message-wrapper').hide();
    <?php } ?>
  });
</script>
