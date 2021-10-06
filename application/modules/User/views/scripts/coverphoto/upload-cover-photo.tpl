<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: upload-cover-photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>
<?php if ($this->photoType !== 'cover'): ?>
  <?php $href = $this->url(array('controller' => 'edit', 'action' => 'profile-photos'), 'user_extended'); ?>
  <script>
    function replaceError(content, newContent) {
      $$('ul.errors li').each( function(li) {
        if (li.innerHTML === content) {
          li.innerHTML = newContent;
        }
      }
    );}
    document.addEventListener('DOMContentLoaded', function() {
      replaceError(
        '<?php echo $this->translate("File creation failed. You may be over your upload limit. Try uploading a smaller file, or delete some files to free up space. ")?>',
        '<?php echo $this->translate(sprintf("File creation failed. You may be over your upload limit. Try uploading a smaller file, or %1sdelete%2s some files to free up space. ",
           "<a href=\'$href\' target=\'_blank\'>", "</a>"))?>'
      );
    }, false);
  </script>
<?php endif; ?>
<?php
  $this->headLink()
    ->appendStylesheet($this->layout()->staticBaseUrl . 'application/modules/Core/externals/styles/coverphoto.css'); ?>
<div class="global_form_popup">
  <div id="form_photo_cover" <?php if ($this->status): ?>class="is_hidden"<?php endif; ?>>
    <?php echo $this->form->setAttrib('class', 'upload_photo_popup_form')->render($this) ?>
  </div>
  <div id="cover_photo_loading" <?php if (!$this->status): ?>class="is_hidden"<?php endif; ?>>
    <div class="cover_photo_loader"></div>
  </div>
</div>

<?php if ($this->status): ?>
  <script type="text/javascript">
  <?php if ($this->photoType == 'cover'): ?>
     parent.document.coverPhoto.getCoverPhoto(1);
  <?php else: ?>
     parent.document.mainPhoto.getMainPhoto();
  <?php endif; ?>
  </script>
<?php else: ?>
<script type="text/javascript">
  function uploadPhoto() {
    $('cover_photo_form').submit();
    $('form_photo_cover').addClass('is_hidden');
    $('cover_photo_loading').removeClass('is_hidden');
  }
</script>
<?php endif; ?>
