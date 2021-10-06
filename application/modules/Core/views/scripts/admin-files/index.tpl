<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10247 2014-05-30 21:34:25Z andres $
 * @author     John
 */
?>
<?php $this->headLink()->appendStylesheet($this->layout()->staticBaseUrl . '/externals/uploader/uploader.css'); ?>

<script type="text/javascript">

  var fileCopyUrl = function(url) {
    Smoothbox.open('<div><input type=\'text\' style=\'width:400px\' /><br /><br /><button onclick="Smoothbox.close();">Close</button></div>', {autoResize : true});
    Smoothbox.instance.content.getElement('input').set('value', url).focus();
    Smoothbox.instance.content.getElement('input').select();
    Smoothbox.instance.doAutoResize();
  }

  var uploadFile = function()
  {
    $('upload_file').click();
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

var BaseFileUpload = {
  uploadFile: function (obj, file, iteration, total) {
    var url = obj.get('data-url');
    var xhr = new XMLHttpRequest();
    var fd = new FormData();
    xhr.open("POST", url, true);
    $('files-status-overall').setStyle('display', 'block');

    // progress bar
    xhr.upload.addEventListener('progress', function (e) {
      var per = (total <= 1 ? e.loaded/e.total : iteration/total) * 100;
      var overAllFileProgress = -400 + ((per) * 2.5);
      $$('div#files-status-overall img')[0].setStyle('background-position', overAllFileProgress + 'px 0px');
      $$('div#files-status-overall span')[0].set('html', per + '%');
    }, false);

    xhr.onreadystatechange = function() {
      if (xhr.readyState === 4 && xhr.status === 200) {
        try {
          var res = JSON.parse(xhr.responseText);
        } catch (err) {
          BaseFileUpload.processUploadError('An error occurred.');
          return false;
        }

        if (res['error'] !== undefined) {
          BaseFileUpload.processUploadError(res['error']);
          return false;
        }
        BaseFileUpload.processUploadSuccess(res);

        if (iteration === total) {
          location.reload();
          //BaseFileUpload.showRefresh();
        }
      }
    };
    fd.append('ajax-upload', 'true');
    fd.append(obj.get('name'), file);
    xhr.send(fd);
  },

  showRefresh: function () {
    $('files-status-overall').setStyle('display', 'none');
    $('upload-complete-message').setStyle('display', '');
  },

  processUploadSuccess: function(response) {
    var uploadedFileList = document.getElementById("uploaded-file-list");
    var uploadedFile = new Element('li', {
      'class': 'file file-success',
      'html' : '<span class="file-name">' + response['fileName'] + '</span>',
    }).inject(uploadedFileList);
    if (uploadedFile.offsetParent === null) {
      uploadedFileList.style.display = "block";
    }
  },

  processUploadError: function(errorMessage) {
    var uploadedFileList = document.getElementById("uploaded-file-list");
    var uploadedFile = new Element('li', {
      'class': 'file file-error',
      'html' : '<span class="validation-error" title="Click to remove this entry.">' + errorMessage + '</span>',
      events: {
        click: function() {
          this.destroy();
        }
      }
    }).inject(uploadedFileList, 'top');
    // If hidden show upload list
    if (uploadedFile.offsetParent === null) {
      uploadedFileList.style.display = "block";
    }
    $('files-status-overall').setStyle('display', 'none');
    return false;
  },
};

en4.core.runonce.add(function () {
  $$('.file-input').each(function (el) {
    $('upload-complete-message').setStyle('display', 'none');
    el.addEvent('change', function () {
      var files = this.files;
      var total = files.length;
      var iteration = 0;
      for(var i = 0; i < files.length; i++) {
        iteration++;
        BaseFileUpload.uploadFile($(el), this.files[i], iteration, total);
      }
    });
    el.addEvent('click', function () {
      this.value = '';
    });
  });
});

function multiDelete() {
  return confirm("<?php echo $this->translate("Are you sure you want to delete the selected files ?") ?>");
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

</script>

<h2><?php echo $this->translate("File & Media Manager") ?></h2>
<p><?php echo $this->translate('You may want to quickly upload images, icons, or other media for use in your layout, announcements, blog entries, etc. You can upload and manage these files here. Move your mouse over a filename to preview an image.') ?></p>
<?php
$settings = Engine_Api::_()->getApi('settings', 'core');
if( $settings->getSetting('user.support.links', 0) == 1 ) {
	echo 'More info: <a href="https://socialengine.atlassian.net/wiki/spaces/SU/pages/5308879/se-php-file-&-media-manager" target="_blank">See KB article</a>';
} 
?>	
<br />	

<br />

<div>
  <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Upload New Files'), array('id' => 'demo-browse', 'class' => 'buttonlink admin_files_upload', 'onclick' => 'uploadFile();')) ?>
</div>

<div id="file-status">
  <div id="files-status-overall" style="display: none;">
    <div class="overall-title">Overall Progress</div>
    <img src='<?php echo $this->layout()->staticBaseUrl . "/externals/fancyupload/assets/progress-bar/bar.gif" ?>' class="progress overall-progress">
    <span>0%</span>
  </div>

  <ul id="uploaded-file-list"></ul>
  <div id="base-uploader-progress"></div>
  <div class="base-uploader">
    <input class="file-input" id="upload_file" type="file" multiple="multiple" data-url="<?php echo $this->url(array('action' => 'upload'))?>" data-form-id="#form-upload" name='Filedata'>
  </div>

  <div id="upload-complete-message" style="display:none;">
    <?php echo $this->htmlLink(array('reset' => false), 'Refresh the page to display new files') ?>
  </div>
</div>
<br />

<div class='admin_search'>
  <?php echo $this->formFilter->render($this) ?>
</div>
<br />

<?php if( count($this->paginator) ): ?>
  <div>
    <?php echo $this->translate(array('%s file found.', '%s files found.', $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?>
  </div>
  
  <?php if($this->existingFiles > 0) { ?>
    <a href="<?php echo $this->url(array('action' => 'sink')) ?>" class="smoothbox sink_fmm_files"><?php echo $this->translate('Sink Existing Files') ?></a>
  <?php } ?>
  <br />
  <form id='multidelete_form' method="post" action="<?php echo $this->url();?>" onSubmit="return multiDelete()">
  <div class="select_fmm_all"><input onclick='selectAll();' type='checkbox' class='checkbox' />Select All</div>
  <ul class="fmm_media_list">
      <?php foreach ($this->paginator as $item): ?>
        <?php $storage = Engine_Api::_()->getItem('storage_file', $item->storage_file_id);
        $path = $storage->map();
        $copyPath = ($storage->service_id == 2) ?  $path : (_ENGINE_SSL ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $path; 
        ?>
        <li class="fmm_media_file">
         <div class="fmm_media_item">
           <div class="fmm_file_checkbox"><input type='checkbox' class='checkbox' name='delete_<?php echo $item->file_id;?>' value='<?php echo $item->file_id ?>' /></div>
          <div class="fmm_file_options">
            <a href="<?php echo $path; ?>" target="_blank" class="fa fmm_icon_preview" title="<?php echo $this->translate('Preview') ?>"></a>
            <a href="<?php echo $this->url(array('action' => 'rename', 'file_id' => $item->file_id)) ?>" class="smoothbox fa fmm_icon_rename" title="<?php echo $this->translate('Rename') ?>"></a>
            <a href="<?php echo $this->url(array('action' => 'delete', 'file_id' => $item->file_id)) ?>" class="smoothbox fa fmm_icon_delete" title="<?php echo $this->translate('Delete') ?>"></a>
            <a href="<?php echo $this->url(array('action' => 'download', 'file_id' => $item->file_id)) ?>" class="fa fmm_icon_download" title="<?php echo $this->translate('Download') ?>"></a>
            <a href="javascript:void(0);" onclick="fileCopyUrl('<?php echo $copyPath; ?>');" class="fa fmm_icon_url" title="<?php echo $this->translate('Copy URL') ?>"></a>
          </div>
          <div class="fmm_file_preview fmm_file_preview_image">
            <?php if($item->extension == 'pdf'): ?>
                <img src="application/modules/Core/externals/images/admin/file-icons/pdf.png">
              <?php elseif(in_array($item->extension, array('text', 'txt'))): ?>
                <img src="application/modules/Core/externals/images/admin/file-icons/text.png">
              <?php elseif(in_array($item->extension, array('mpeg', 'x-realaudio', 'wav', 'amr', 'mp3', 'ogg','midi','x-ms-wma', 'x-ms-wax', 'x-matroska'))): ?>
                <img src="application/modules/Core/externals/images/admin/file-icons/audio.png">
              <?php elseif(in_array($item->extension, array('mp4', 'flv'))): ?>
                <img src="application/modules/Core/externals/images/admin/file-icons/video.png">
              <?php elseif(in_array($item->extension, array('zip', 'tar'))): ?>
                <img src="application/modules/Core/externals/images/admin/file-icons/zip.png">
              <?php elseif(in_array($item->extension, array('jpeg', 'jpg', 'gif', 'png', 'tiff'))): ?>
                <img src="<?php echo $path; ?>" />
              <?php else: ?>
                <img src="application/modules/Core/externals/images/admin/file-icons/default.png">
               <?php endif; ?>
          </div>
          <div class="fmm_file_name"><?php echo $item->name . '.'.$item->extension; ?></div>
          </div>
        </li>
      <?php endforeach; ?>
      </ul>
  <br />
  <div class='buttons'>
    <button type='submit'><?php echo $this->translate("Delete Selected") ?></button>
  </div>
  </form>
  <br />
  <div>
    <?php echo $this->paginationControl($this->paginator); ?>
  </div>
<?php else: ?>
  <br />
  <div class="tip">
    <span>
      <?php echo $this->translate("There are no files uploaded by you yet.") ?>
    </span>
  </div>
<?php endif; ?> 
