<?php ?>

<?php  
  $this->headScript()->appendFile($this->layout()->staticBaseUrl . 'externals/jscolor/jscolor.js')
      ->appendFile($this->layout()->staticBaseUrl . 'externals/jQuery/jquery.min.js'); 
?>

<h2><?php echo $this->translate('Serenity Theme') ?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class='clear'>
  <div class='settings serenity_color_schemes_form'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>
<?php $theme_color = Engine_Api::_()->serenity()->getContantValueXML('theme_color'); ?>
<script type="text/javascript">

  hashSign = '#';

  window.addEvent('domready', function() {
    scriptJquery('#theme_color-<?php echo $theme_color ?>').parent().addClass('colored-border');
    changeThemeColor("<?php echo $theme_color; ?>");
  });
  
  function getThemeColor(value) {
    var URL = en4.core.staticBaseUrl+'serenity/admin-settings/getcustomthemecolors/';
    (new Request.HTML({
      method: 'post',
      'url': URL ,
      'data': {
        format: 'html',
        customtheme_id: value,
      },
      onSuccess: function(responseTree, responseElements, responseHTML, responseJavaScript) {
        var customthevalyearray = scriptJquery.parseJSON(responseHTML);
        for(i=0;i<customthevalyearray.length;i++){
          var splitValue = customthevalyearray[i].split('||');
          scriptJquery('#'+splitValue[0]).val(splitValue[1]);
          if(scriptJquery('#'+splitValue[0]).hasClass('SEcolor')){
            if(splitValue[1] == ""){
              splitValue[1] = "#FFFFFF";
            }
            try{
              document.getElementById(splitValue[0]).color.fromString('#'+splitValue[1]);
              document.getElementById(splitValue[0]).style.color = '#'+splitValue[1];
            } catch(err) {
              document.getElementById(splitValue[0]).color.fromString('#'+splitValue[1]);
              document.getElementById(splitValue[0]).style.color = '#'+splitValue[1];
            }
          }
        }
      }
    })).send();
  }

	function changeThemeColor(value) {
    
    getThemeColor(value);
    
	  if(value == 1 || value == 2 || value == 3) {
		  if($('header_settings-wrapper'))
				$('header_settings-wrapper').style.display = 'none';
	    if($('footer_settings-wrapper'))
				$('footer_settings-wrapper').style.display = 'none';
		  if($('body_settings-wrapper'))
				$('body_settings-wrapper').style.display = 'none';
			if($('header_settings_group'))
			  $('header_settings_group').style.display = 'none';
			if($('footer_settings_group'))
			  $('footer_settings_group').style.display = 'none';
			if($('body_settings_group'))
			  $('body_settings_group').style.display = 'none';
      if($('custom_themes'))
				$('custom_themes').style.display = 'block';
      if($('edit_custom_themes'))
        $('edit_custom_themes').style.display = 'none';
      if($('delete_custom_themes'))
        $('delete_custom_themes').style.display = 'none';
      if($('deletedisabled_custom_themes'))
        $('deletedisabled_custom_themes').style.display = 'none';
      if($('submit'))
        $('submit').style.display = 'none';
	  } else {
		  if($('header_settings-wrapper'))
				$('header_settings-wrapper').style.display = 'block';
	    if($('footer_settings-wrapper'))
				$('footer_settings-wrapper').style.display = 'block';
			if($('body_settings-wrapper'))
				$('body_settings-wrapper').style.display = 'block';
			if($('header_settings_group'))
			  $('header_settings_group').style.display = 'block';
			if($('footer_settings_group'))
			  $('footer_settings_group').style.display = 'block';
			if($('body_settings_group'))
			  $('body_settings_group').style.display = 'block';
				
      if(value > 3) {
        if($('submit'))
          $('submit').style.display = 'inline-block';
        if($('edit_custom_themes'))
          $('edit_custom_themes').style.display = 'inline-block';
        if($('delete_custom_themes'))
          $('delete_custom_themes').style.display = 'inline-block';

        <?php if(empty($this->customtheme_id)): ?>
          history.pushState(null, null, 'admin/serenity/settings/styling/customtheme_id/'+value);
          scriptJquery('#edit_custom_themes').attr('href', 'serenity/admin-settings/add/customtheme_id/'+value);
          scriptJquery('#delete_custom_themes').attr('href', 'serenity/admin-settings/delete/customtheme_id/'+value);
        <?php else: ?>
          scriptJquery('#edit_custom_themes').attr('href', 'serenity/admin-settings/add/customtheme_id/'+value);
          
          var activatedTheme = '<?php echo $this->activatedTheme; ?>';
          if(activatedTheme == value) {
            $('delete_custom_themes').style.display = 'none';
            $('deletedisabled_custom_themes').style.display = 'inline-block';
          } else {
            if($('deletedisabled_custom_themes'))
              $('deletedisabled_custom_themes').style.display = 'none';
            scriptJquery('#delete_custom_themes').attr('href', 'serenity/admin-settings/delete/customtheme_id/'+value);
          }
        <?php endif; ?>
      } else {
        if($('edit_custom_themes'))
          $('edit_custom_themes').style.display = 'none';
        if($('delete_custom_themes'))
          $('delete_custom_themes').style.display = 'none';
        if($('deletedisabled_custom_themes'))
          $('deletedisabled_custom_themes').style.display = 'none';
        if($('submit'))
          $('submit').style.display = 'none';
      }
	  }
	}
	
	scriptJquery(document).ready(function() {
    scriptJquery('#theme_color-element .form-options-wrapper > li').click(function(){
      if ( scriptJquery(this).hasClass('colored-border') ) {
          scriptJquery(this).removeClass('colored-border');
      } else {
          scriptJquery('#theme_color-element .form-options-wrapper > li.colored-border').removeClass('colored-border');
          scriptJquery(this).addClass('colored-border');    
      }
    });
	});
</script>
