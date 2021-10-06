<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: get-cover-photo.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Alex
 */
?>

<?php $levelId = $this->user->level_id;
  $userId = $this->user->user_id;
  
  $coverphoto = Engine_Api::_()->authorization()->getPermission($levelId, 'user', 'coverphoto');
  $coverphoto = $coverphoto ? Engine_Api::_()->core()->getFileUrl($coverphoto) : '';
  ?>
<?php if (empty($this->uploadDefaultCover)) : ?>
  <?php if (Engine_Api::_()->authorization()->isAllowed('user', $this->user, 'coverphotoupload') && $this->photo) : ?>
    <div class="profile_cover_photo cover_photo_wap b_dark">
      <?php echo $this->itemPhoto($this->photo, 'thumb.cover', '', array(
        'align' => 'left',
        'class' => 'cover_photo',
        'style' => 'top:' . $this->topPosition . 'px'
      )); ?>
      <?php if (!empty($this->can_edit)) : ?>
        <div class="cover_tip_wrap is_hidden">
          <div class="cover_tip">
            <?php echo $this->translate("Drag to Reposition Cover Photo") ?>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php elseif (!empty($coverphoto)) : ?>
    <div class="profile_cover_photo cover_photo_wap b_dark">
      <img src="<?php echo $coverphoto; ?>" align="left" class="cover_photo" style="top:<?php echo $this->topPosition?>px" />
    </div>
  <?php elseif (!empty($coverId = Engine_Api::_()->getApi("settings", "core")
          ->getSetting("usercoverphoto.preview.level.id.$levelId"))) : ?>
          <div class="profile_cover_photo cover_photo_wap b_dark">
            <img src="<?php echo Engine_Api::_()->storage()->get($coverId, 'thumb.cover')->map()?>"
              align="left" class="cover_photo" style="top:<?php echo $this->topPosition?>px" />
          </div>
  <?php else : ?>
    <div class="profile_cover_photo_empty"></div>
  <?php endif; ?>

  <?php if (!empty($this->can_edit)) : ?>
    <div id="cover_photo_options" class="profile_cover_options">
      <ul class="edit-button">
        <li>
          <?php if (!empty($this->user->coverphoto) && $this->photo) : ?>
            <span class="profile_cover_btn">
              <i class="fa fa-camera" aria-hidden="true"></i>
            </span>
          <?php else: ?>
            <span class="profile_cover_btn">
              <i class="fa fa-camera" aria-hidden="true"></i>
            </span>
          <?php endif; ?>

          <ul class="profile_options_pulldown">
            <li>
              <a href='<?php echo $this->url(array('action' => 'upload-cover-photo', 'user_id' => $userId), 'user_coverphoto', true); ?>'
                class="profile_cover_icon_photo_upload smoothbox">
                <?php echo $this->translate('Upload Cover Photo'); ?>
              </a>
            </li>
            <?php if (Engine_Api::_()->getDbtable('modules', 'core')->isModuleEnabled('album')):?>
              <li>
                <?php echo $this->htmlLink(
                  array('route' => 'user_coverphoto', 'action' => 'choose-from-albums', 'user_id' => $userId),
                  $this->translate('Choose from Albums'),
                  array(' class' => 'profile_cover_icon_photo_view smoothbox')
                ); ?>
              </li>
            <?php endif; ?>
            <?php if (!empty($this->user->coverphoto) && $this->photo) : ?>
              <li><a href="javascript:document.coverPhoto.reposition.start()" class="cover_reposition profile_cover_icon_move">
                      <?php echo $this->translate("Reposition"); ?></a>
              </li>
              <li>
                <?php echo $this->htmlLink(
                  array('route' => 'user_coverphoto', 'action' => 'remove-cover-photo', 'user_id' => $userId),
                  $this->translate('Remove Cover Photo'),
                  array(' class' => 'smoothbox profile_cover_icon_photo_delete')
                ); ?>
              </li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
      <?php if (!empty($this->user->coverphoto)) : ?>
        <div class="save-button is_hidden">
          <span class="save-positions">
            <button><?php echo $this->translate("Save Position"); ?></button>
          </span>
          <span class="cancel">
            <button><?php echo $this->translate("Cancel"); ?></button>
          </span>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
	<div class="clr"></div>
<?php else : ?>
  <?php $levelId = $this->level_id;?>
  <div class="profile_cover_photo cover_photo_wap b_dark">
    <?php if(!empty($coverId = Engine_Api::_()->getApi("settings", "core")->getSetting("usercoverphoto.preview.level.id.$levelId"))) : ?>
      <img src="<?php echo Engine_Api::_()->storage()->get($coverId, 'thumb.cover')->map()?>"
        align="left" class="cover_photo" style="top:<?php echo $this->topPosition?>px" />
        <div class="cover_tip_wrap is_hidden">
          <div class="cover_tip">
            <?php echo $this->translate("Drag to Reposition Default Cover Photo") ?>
          </div>
        </div>
    <?php else : ?>
      <div class="profile_cover_photo_empty"></div>
    <?php endif; ?>
  </div>
  <div id="cover_photo_options" class="profile_cover_options">
    <ul class="edit-button">
      <li>
        <?php if (Engine_Api::_()->getApi("settings", "core")->getSetting("usercoverphoto.preview.level.id.$levelId")) : ?>
          <span class="profile_cover_btn">
            <i class="fa fa-camera" aria-hidden="true"></i>
          </span>
        <?php else : ?>
          <span class="profile_cover_btn">
            <i class="fa fa-camera" aria-hidden="true"></i>
          </span>
        <?php endif; ?>
          <ul class="profile_options_pulldown">
            <li>
              <a href='<?php echo $this->url(array(
                'action' => 'upload-cover-photo',
                'user_id' => $userId,
                'photoType' => 'cover',
                'uploadDefaultCover' => $this->uploadDefaultCover,
                'level_id' => $this->level_id), 'user_coverphoto', true); ?>' class="profile_cover_icon_photo_upload smoothbox">
                <?php echo $this->translate('Upload Default Cover Photo'); ?>
              </a>
            </li>

            <?php if (Engine_Api::_()->getApi("settings", "core")->getSetting("usercoverphoto.preview.level.id.$levelId")) : ?>
              <li><a  href="javascript:document.coverPhoto.reposition.start()" class="cover_reposition profile_cover_icon_move">
                  <?php echo $this->translate("Reposition"); ?></a>
              </li>
              <li>
                <?php echo $this->htmlLink(
                  array(
                    'route' => 'user_coverphoto',
                    'action' => 'remove-cover-photo',
                    'user_id' => $userId,
                    'uploadDefaultCover' => $this->uploadDefaultCover,
                    'level_id' => $this->level_id
                  ),
                  $this->translate('Remove Default Cover Photo'),
                  array(' class' => 'smoothbox profile_cover_icon_photo_delete')
                ); ?>
              </li>
            <?php endif; ?>
          </ul>
      </li>
    </ul>
    <?php if (!empty($this->uploadDefaultCover)) : ?>
      <div class="save-button is_hidden">
        <span class="save-positions">
          <button><?php echo $this->translate("Save Position"); ?></button>
        </span>
        <span class="cancel">
          <button><?php echo $this->translate("Cancel"); ?></button>
        </span>
      </div>
    <?php endif; ?>
  </div>
  <div class="clr"></div>
<?php endif;?>
