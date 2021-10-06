<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: account.tpl 10143 2014-03-26 16:18:25Z andres $
 * @author     John
 */
?>

<style>
#signup_account_form #name-wrapper {
  display: none;
}
</style>

<script type="text/javascript">
//<![CDATA[
  scriptJquery(window).load(function() {
    if( scriptJquery('#username') && scriptJquery('#profile_address') ) {
      var profile_address = scriptJquery('#profile_address').html();
      profile_address = profile_address.replace('<?php echo /*$this->translate(*/'yourname'/*)*/?>',
          '<span id="profile_address_text"><?php echo $this->translate('yourname') ?></span>');
      scriptJquery('#profile_address').html(profile_address);

      scriptJquery(document).on('keyup','#username', function() {
        var text = '<?php echo $this->translate('yourname') ?>';
        if( this.value != '' ) {
          text = this.value;
        }
        scriptJquery('#profile_address_text').html(text.replace(/[^a-z0-9]/gi,''));
      });
      // trigger on page-load
      if( document.getElementById('username').value.length ) {
        document.getElementById('username').fireEvent('keyup');
      }
    }
  });
//]]>
</script>

<?php echo $this->form->render($this) ?>

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
