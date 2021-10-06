<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: receipt.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */
?>
<form method="post">
<div class='clear'>
  <div class='settings global_form_popup'>
    <h2>Receipt</h2>
    <div>
      <div id="buttons-wrapper" class="form-wrapper">
        <div id="buttons-element" class="form-element">
          <?php echo $this->itemPhoto($this->transaction, null); ?>
         <!-- <img src="" alt="" />-->
        </div>
      </div>
    </div>
  </div>
</div>
</form>
