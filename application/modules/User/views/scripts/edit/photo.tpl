<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
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
<div class="layout_middle">
  <div class="generic_layout_container">
   <div class="headline">
    <h2>
    <?php if ($this->viewer->isSelf($this->user) ):?>
      <?php echo $this->translate('Edit My Profile');?>
    <?php else:?>
      <?php echo $this->translate('%1$s\'s Profile', $this->htmlLink($this->user->getHref(), $this->user->getTitle()));?>
    <?php endif;?>
  </h2>
  <div class="tabs">
    <?php
      // Render the menu
      echo $this->navigation()
        ->menu()
        ->setContainer($this->navigation)
        ->render();
    ?>
  </div>
</div>
</div>
</div>
<div class="layout_middle">
  <div class="generic_layout_container">
<?php echo $this->form->render($this) ?>
</div>
</div>
