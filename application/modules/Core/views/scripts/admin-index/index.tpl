<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="admin_home_wrapper">
  <div class="admin_home_right">
    <?php echo $this->content()->renderWidget('core.admin-statistics') ?>
    <?php echo $this->content()->renderWidget('core.admin-environment') ?>
  </div>
  <div class="admin_home_middle">
    <?php echo $this->content()->renderWidget('core.admin-dashboard') ?>
    <?php echo $this->content()->renderWidget('core.admin-content-show') ?>
    <?php if(Engine_Api::_()->getApi('settings', 'core')->getSetting('core.newsupdates')) { ?>
      <?php echo $this->content()->renderWidget('core.admin-news') ?>
    <?php } ?>
  </div>
</div>
