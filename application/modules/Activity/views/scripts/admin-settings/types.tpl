<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: types.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<script type="text/javascript">
  var url = en4.core.baseUrl+'admin/activity/settings/types/plugin/';
  var fetchActivitySettings = function(type, plugin) {
    window.location.href = url + plugin + '/type/' + type;
  }

  var fetchActivityTypes = function(plugin) {
    window.location.href = url + plugin;
  }
</script>

<div class='settings'>
  <?php echo $this->form->render($this); ?>
</div>
