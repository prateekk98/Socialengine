<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: profile-photos.tpl 10247 2014-05-30 21:34:25Z andres $
 * @author     John
 */
?>
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
<script type="text/javascript">
  var previewFileForceOpen;
  var check_selected = function () {
    try {
      $$('input[type=checkbox]').each(function(el) {
        if (el.id != 'checkall' && el.checked == true) {
          throw true;
        }
      });
      alert("No entry selected to delete.");
      return false;
    } catch (e) {
      return confirm("<?php echo $this->translate('Are you sure you want to delete selected files?');?>");
    }
  }

  function selectAll() {
    var i;
    var multidelete_form = $('multidelete_form');
    var inputs = multidelete_form.elements;
    for (i = 1; i < inputs.length; i++) {
      if (!inputs[i].disabled) {
        inputs[i].checked = inputs[0].checked;
      }
    }
  }

  var previewFile = function(event)
  {
    event = new Event(event);
    element = $(event.target).getParent('.admin_file').getElement('.admin_file_preview');

    // Ignore ones with no preview
    if( !element || element.getChildren().length < 1 ) {
      return;
    }

    if( event.type == 'click' ) {
      if( previewFileForceOpen ) {
        previewFileForceOpen.setStyle('display', 'none');
        previewFileForceOpen = false;
      } else {
        previewFileForceOpen = element;
        previewFileForceOpen.setStyle('display', 'block');
      }
    }
    if( previewFileForceOpen ) {
      return;
    }

    var targetState = ( event.type == 'mouseover' ? true : false );
    element.setStyle('display', (targetState ? 'block' : 'none'));
  }

  window.addEvent('load', function() {
    $$('.admin_file_name').addEvents({
      click : previewFile,
      mouseout : previewFile,
      mouseover : previewFile
    });
    $$('.admin_file_preview').addEvents({
      click : previewFile
    });
  });

</script>
<div class="layout_middle">
  <div class="generic_layout_container">
<h3><?php echo $this->translate("Delete profile photos"); ?></h3>

<?php if(count($this->paginator) > 0): ?>
  <p><?php echo $this->translate("Below, you can delete your previously uploaded profile photos and they will no longer be part of your space."); ?></p>
  <br/>
  <div class="profile_photo_wrapper">
    <div class="paginator_pages">
      <?php $pageInfo = $this->paginator->getPages(); ?>
      <?php echo $this->translate(array('Showing %s-%s of %s file.', 'Showing %s-%s of %s files.', $pageInfo->totalItemCount),
          $pageInfo->firstItemNumber, $pageInfo->lastItemNumber, $pageInfo->totalItemCount) ?>
      <span>
        <?php if( !empty($pageInfo->previous) ): ?>
          <?php echo $this->htmlLink(array('reset' => false, 'APPEND' => '?page=' . $pageInfo->previous), 'Previous Page') ?>
        <?php endif; ?>
        <?php if( !empty($pageInfo->previous) && !empty($pageInfo->next) ): ?>
           |
        <?php endif; ?>
        <?php if( !empty($pageInfo->next) ): ?>
          <?php echo $this->htmlLink(array('reset' => false, 'APPEND' => '?page=' . $pageInfo->next), 'Next Page') ?>
        <?php endif; ?>
      </span>
    </div>
    <form id="multidelete_form" action="<?php echo $this->url(array('action' => 'delete-profile-photos')) ?>" method="post" onsubmit="return check_selected();">
      <table class='profile_photos_table'>
        <thead>
          <tr>
            <th style="width: 1%"><input onclick='selectAll();' type='checkbox' class='checkbox' /></th>
            <th><?php echo $this->translate("Name") ?></th>
            <th><?php echo $this->translate("Options") ?></th>
          </tr>
        </thead>
        <tbody class="profile_photos">
          <?php foreach ($this->paginator as $file): ?>
          <tr class="admin_file">
              <td><input type='checkbox' class='checkbox' name='photo_ids[]' id='photo_id' value="<?php echo $file['file_id']; ?>" /></td>
              <td>
                  <div class="admin_file_name">
                      <?php echo $file['name'] ?>
                  </div>
                  <div class="admin_file_preview" style="display:none">
                    <?php echo $this->htmlImage($this->baseUrl() . '/' . $file['storage_path'], $file['name']) ?>
                  </div></td>
              <td>
                <a href="<?php echo $this->url(array('action' => 'delete-profile-photos', 'photo_ids' => $file['file_id'])) ?>" class="icon_photos_delete"> <?php echo $this->translate('delete') ?></a>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <br />
      <div class='buttons'>
        <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
      </div>
    </form>
  </div>
<?php else: ?>
    <div class="tip">
      <span><?php echo $this->translate("No profile photo found"); ?></span>
    </div>
<?php endif; ?>
</div>
</div>
