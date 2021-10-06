<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: createad.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */
?>
<script type="text/javascript">
//<![CDATA[
var updateTextFields = function() {
  if ($$('#mediatype-0:checked').length) {
    $('upload_image-wrapper').show();
    $('html_field-wrapper').hide();
    $('submit-wrapper').show();
  } else if ($$('#mediatype-1:checked').length) {
    $('upload_image-wrapper').hide();
    $('html_field-wrapper').show();
    $('submit-wrapper').show();
  } else {
    $('upload_image-wrapper').hide();
    $('html_field-wrapper').hide();
    $('submit-wrapper').hide();
  } 
};

  en4.core.runonce.add(function () {
    var uploaderInstance = new Uploader('upload_file', {
      uploadLinkClass : 'buttonlink icon_photos_new',
      uploadLinkTitle : '<?php echo $this->translate("Add Photos");?>',
      uploadLinkDesc : '<?php echo $this->translate('CORE_VIEWS_SCRIPTS_FANCYUPLOAD_ADDPHOTOS');?>'
    });
    uploaderInstance['processCustomResponse'] = function (responseData) {
      $('photo_id').value =  responseData.photo_id;
      var html_code_element = document.getElementById("html_field-wrapper");
      html_code_element.style.display = "block";
      $('html_code').value = responseData.photo_url;
    };
  });

  var deleteFile = function (el) {console.log(el);
    var photo_id = el.get('data-file_id');console.log(photo_id);
    el.getParent('li').destroy();
    new Request.JSON({
         'url' : '<?php echo $this->url(Array('module'=>'core', 'controller' => 'admin-ads', 'action'=>'removephoto'), 'default') ?>',
      data: {
        'format': 'json',
        'photo_id': photo_id,
      },
      'onSuccess' : function(responseJSON) {
        var html_code_element = document.getElementById("html_field-wrapper");
        html_code_element.style.display = "none";
      }
    }).send();
  }

var preview = function(){
  var code = $('html_code').value;
  var preview = new Element('div', {
    'html': code,
    'styles': {
        'height': 'auto',
        'width' : 'auto'
    }
  });
  //if ($type(console)) console.log(preview.getAttribute('width'));
  Smoothbox.open(preview);
}
en4.core.runonce.add(updateTextFields);
//]]>
</script>
<h2><?php echo $this->translate("Editing Ad Campaign: ") ?><?php echo $this->campaign->name;?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php
      // Render the menu
      //->setUlClass()
      echo $this->navigation()->menu()->setContainer($this->navigation)->render()
    ?>
  </div>
<?php endif; ?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>
