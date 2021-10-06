<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Authorization
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    https://www.socialengine.com/license/
 * @version    $Id: map-profile-type.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if (!empty($this->message)): ?>
    <div class="tip" style="margin:20px 10px 0 10px;">
        <span>
            <?php echo $this->message; ?>
        </span>
    </div>
<?php else: ?>
    <div class="settings">
      <?php echo $this->form->render($this) ?>
    </div>
<?php endif; ?>
