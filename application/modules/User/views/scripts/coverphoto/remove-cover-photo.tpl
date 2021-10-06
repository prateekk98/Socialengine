<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: remove-cover-photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>

<form method="post" class="global_form_popup">
  <div>
    <?php if ($this->photoType == 'cover') : ?>
      <?php if(empty($this->uploadDefaultCover)):?>
        <h3><?php echo $this->translate('Delete Cover Photo?'); ?></h3>
        <p><?php echo $this->translate("Are you sure you want to delete this cover photo?"); ?></p>
      <?php else: ?>
        <h3><?php echo $this->translate('Delete Default Cover Photo?'); ?></h3>
        <p><?php echo $this->translate("Are you sure you want to delete Default Cover Photo?"); ?></p><br />
      <?php endif; ?>
    <?php else: ?>
      <h3><?php echo $this->translate('Remove Profile Picture?'); ?></h3>
      <p><?php echo $this->translate("Are you sure you want to remove your Profile Picture?"); ?></p>
    <?php endif; ?>
    <br />
    <p>
      <input type="hidden" name="confirm" value=""/>
      <button type='submit'><?php echo $this->translate('Remove'); ?></button>
      <?php echo $this->translate(" or "); ?> <a href='javascript:void(0);' onclick='javascript:parent.Smoothbox.close()'><?php echo $this->translate('cancel'); ?></a>
    </p>
  </div>
</form>

<?php if (@$this->closeSmoothbox): ?>
  <script type="text/javascript">
    TB_close();
  </script>
<?php endif; ?>
