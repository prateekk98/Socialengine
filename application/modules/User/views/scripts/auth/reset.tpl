<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: reset.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if( empty($this->reset) ): ?>
 <div class="layout_middle">
   <div class="generic_layout_container">
     <?php echo $this->form->render($this) ?>
   </div>
 </div>

 <script type="text/javascript">
  function passwordRoutine(value){
      var pswd = value;
      // valid length
      if ( pswd.length < 6) {
        scriptJquery('#passwordroutine_length').removeClass('valid').addClass('invalid');
      } else {
        scriptJquery('#passwordroutine_length').removeClass('invalid').addClass('valid');
      }
      //validate special character
      if ( pswd.match(/[#?!@$%^&*-]/) ) {
          if ( pswd.match(/[\\\\:\/]/) ) {
              scriptJquery('#passwordroutine_specialcharacters').removeClass('valid').addClass('invalid');
          } else {
              scriptJquery('#passwordroutine_specialcharacters').removeClass('invalid').addClass('valid');
          }
      } else {
          scriptJquery('#passwordroutine_specialcharacters').removeClass('valid').addClass('invalid');
      }
      //validate capital letter
      if ( pswd.match(/[A-Z]/) ) {
          scriptJquery('#passwordroutine_capital').removeClass('invalid').addClass('valid');
      } else {
          scriptJquery('#passwordroutine_capital').removeClass('valid').addClass('invalid');
      }
      //validate small letter
      if ( pswd.match(/[a-z]/) ) {
          scriptJquery('#passwordroutine_lowerLetter').removeClass('invalid').addClass('valid');
      } else {
          scriptJquery('#passwordroutine_lowerLetter').removeClass('valid').addClass('invalid');
      }
      //validate number
      if ( pswd.match(/\d{1}/) ) {
          scriptJquery('#passwordroutine_number').removeClass('invalid').addClass('valid');
      } else {
          scriptJquery('#passwordroutine_number').removeClass('valid').addClass('invalid');
      }
  }
</script>

<?php else: ?>

  <div class="tip">
    <span>
      <?php echo $this->translate("Your password has been reset. Click %s to sign-in.", $this->htmlLink(array('route' => 'user_login'), $this->translate('here'))) ?>
    </span>
  </div>

<?php endif; ?>

