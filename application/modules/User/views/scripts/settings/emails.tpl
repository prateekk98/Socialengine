<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: emails.tpl 9871 2013-02-12 22:47:33Z shaun $
 * @author     Steve
 */
?>
<?php  $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/jquery.min.js') ?>

<?php echo $this->form->render($this) ?>

<script type="application/javascript">

    <?php if(!empty($this->user->disable_email)) { ?>
        scriptJquery(document).ready(function() {
            scriptJquery('.email_settings').attr('disabled', true);
        });
    <?php } ?>

    function disableEmail(value) {
        if(value.checked == true) {
            scriptJquery('.email_settings').attr('disabled', true);
        } else {
            scriptJquery('.email_settings').attr('disabled', false);
        }
    }
</script>
