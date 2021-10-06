<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: password.tpl 9869 2013-02-12 22:37:42Z shaun $
 * @author     Steve
 */
?>
<?php if(!empty($_SESSION['requirepassword'] )){ ?>
    <div class="require_password">
        <?php echo $this->content()->renderWidget('core.menu-logo',array('disableLink'=>true)); ?>
        <?php echo $this->form->render($this) ?>
    </div>
<?php }else{ ?>
<?php echo $this->form->render($this) ?>
<?php } ?>

<script type="text/javascript">
    function passwordRoutine(value){
        var pswd = value;
        // valid length
        if ( pswd.length < 6) {
            $('passwordroutine_length').removeClass('valid').addClass('invalid');
        } else {
            $('passwordroutine_length').removeClass('invalid').addClass('valid');
        }

        //validate special character
        if ( pswd.match(/[#?!@$%^&*-]/) ) {
            if ( pswd.match(/[\\\\:\/]/) ) {
                $('passwordroutine_specialcharacters').removeClass('valid').addClass('invalid');
            } else {
                $('passwordroutine_specialcharacters').removeClass('invalid').addClass('valid');
            }
        } else {
            $('passwordroutine_specialcharacters').removeClass('valid').addClass('invalid');
        }

        //validate capital letter
        if ( pswd.match(/[A-Z]/) ) {
            $('passwordroutine_capital').removeClass('invalid').addClass('valid');
        } else {
            $('passwordroutine_capital').removeClass('valid').addClass('invalid');
        }

        //validate small letter
        if ( pswd.match(/[a-z]/) ) {
            $('passwordroutine_lowerLetter').removeClass('invalid').addClass('valid');
        } else {
            $('passwordroutine_lowerLetter').removeClass('valid').addClass('invalid');
        }

        //validate number
        if ( pswd.match(/\d{1}/) ) {
            $('passwordroutine_number').removeClass('invalid').addClass('valid');
        } else {
            $('passwordroutine_number').removeClass('valid').addClass('invalid');
        }
    }
</script>
