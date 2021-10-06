<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: approve.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<form method="post">
<div class='clear'>
  <div class='settings global_form_popup'>
    <h2><?php echo $this->translate('Approve Payment'); ?></h2>
    <p><?php echo $this->translate('Are you sure that you want to approve this payment?'); ?></p>
    <div>
      <div id="buttons-wrapper" class="form-wrapper">
        <div id="buttons-element" class="form-element">
          <button name="submit" id="submit" type="submit"><?php echo $this->translate('Approve Payment'); ?></button>
          <?php echo $this->translate('or'); ?> <a name="cancel" id="cancel" type="button" href="javascript:void(0);" onclick="javascript:parent.Smoothbox.close()"><?php echo $this->translate('cancel'); ?></a></div></div>
    </div>
  </div>
</div>
</form>
