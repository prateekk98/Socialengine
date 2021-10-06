<?php ?>

<h2><?php echo $this->translate('Serenity Theme') ?></h2>

<?php if( count($this->navigation) ): ?>
  <div class='tabs'>
    <?php echo $this->navigation()->menu()->setContainer($this->navigation)->render(); ?>
  </div>
<?php endif; ?>

<div class='clear'>
  <div class='settings'>
    <?php echo $this->form->render($this); ?>
  </div>
</div>

<script>
  window.addEvent('domready',function() {
    usegooglefont('<?php echo Engine_Api::_()->getApi('settings', 'core')->getSetting('serenity.googlefonts', 1);?>');
  });
  
  function usegooglefont(value) {
    if(value == 1) {
      if($('serenity_bodygrp'))
        $('serenity_bodygrp').style.display = 'none';
      if($('serenity_headinggrp'))
        $('serenity_headinggrp').style.display = 'none';
      if($('serenity_mainmenugrp'))
        $('serenity_mainmenugrp').style.display = 'none';
      if($('serenity_tabgrp'))
        $('serenity_tabgrp').style.display = 'none';
      if($('serenity_googlebodygrp'))
        $('serenity_googlebodygrp').style.display = 'block';
      if($('serenity_googleheadinggrp'))
        $('serenity_googleheadinggrp').style.display = 'block';
      if($('serenity_googlemainmenugrp'))
        $('serenity_googlemainmenugrp').style.display = 'block';
      if($('serenity_googletabgrp'))
        $('serenity_googletabgrp').style.display = 'block';
    } else {
      if($('serenity_bodygrp'))
        $('serenity_bodygrp').style.display = 'block';
      if($('serenity_headinggrp'))
        $('serenity_headinggrp').style.display = 'block';
      if($('serenity_mainmenugrp'))
        $('serenity_mainmenugrp').style.display = 'block';
      if($('serenity_tabgrp'))
        $('serenity_tabgrp').style.display = 'block';
      if($('serenity_googlebodygrp'))
        $('serenity_googlebodygrp').style.display = 'none';
      if($('serenity_googleheadinggrp'))
        $('serenity_googleheadinggrp').style.display = 'none';
      if($('serenity_googlemainmenugrp'))
        $('serenity_googlemainmenugrp').style.display = 'none';
      if($('serenity_googletabgrp'))
        $('serenity_googletabgrp').style.display = 'none';
    }
  }
</script>
