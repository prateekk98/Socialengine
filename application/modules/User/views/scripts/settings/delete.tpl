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

<?php if( $this->isSuperAdmin ):?>
  <div class="tip">
    <span>
      <?php echo $this->translate('Super Admins can\'t be deleted.'); ?>
    </span>
  </div>
<?php return; endif; ?>

<?php echo $this->form->setAttrib('id', 'user_form_settings_delete')->render($this) ?>

<script type="text/javascript">
	scriptJquery("#send").click(function(e){
		e.preventDefault();
		let url = new URL(window.location.href);
		url.searchParams.set('code', 1);
		window.location.href = url.toString();
	});
</script>