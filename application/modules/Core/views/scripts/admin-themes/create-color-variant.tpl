<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: clone.tpl 9747 2017-02-01 02:08:08Z john $
 * @author     Steve
 */
?>

<div class="settings">
  <?php echo $this->form->render($this) ?>
</div>

<script type="text/javascript">
  var fetchColorVariant = function(variantName) {
    var url = en4.core.baseUrl+'admin/themes/create-color-variant/name/';
    window.location.href = url + variantName;
  }

  var showSubmit = function() {
    $('submitWrapper').setStyle('display', 'block');
  }

</script>
