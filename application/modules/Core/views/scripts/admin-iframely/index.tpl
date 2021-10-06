<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2017-01-26 02:08:08Z john $
 * @author     John
 */
?>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
<script type="text/javascript">
//<![CDATA[
  function updateFields() {
    var checkedValue = $$('input[name=host]:checked')[0].get('value');
    $('secretIframelyKey-wrapper').hide();
    $('baseUrl-wrapper').hide();
    if( '<?php echo Engine_Iframely::IFRAMELY_HOST ?>' == checkedValue ) {
      $('secretIframelyKey-wrapper').show();
    }
    if ( '<?php echo Engine_Iframely::OWN_HOST ?>' == checkedValue ) {
      $('baseUrl-wrapper').show();
    }
  }
  updateFields();
//]]>
</script>
