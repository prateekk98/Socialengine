<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Storage
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
?>
<div id="file-status">
  <a class="buttonlink icon_clearlist" href="javascript:void(0);" id="remove_all_files" style="display:none">
    <?php echo $this->translate('Clear Lists'); ?>
  </a>
  <div id="files-status-overall" style="display: none;">
      <div class="overall-title">Upload Progress</div>
      <img src="<?php echo $this->baseUrl() . '/application/modules/Core/externals/images/progress-bar/bar.gif';?>" class="progress overall-progress" />
      <span>0%</span>
  </div>
</div>

<ul id="uploaded-file-list"></ul>

<input type="hidden" name="<?php echo $this->name; ?>" id="fancyuploadfileids" value="" />
<div id="base-uploader-progress"></div>
<div class="base-uploader">
  <input class="file-input" id="upload_file" type="file" <?php echo $this->context; ?>>
</div>
