<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: preview.tpl 9747 2012-07-26 02:08:08Z john $
* @author     John
*/
?>
<div>
    <?php echo $this->content()->renderWidget('core.banner', array(
    'banner_id' => $this->banner->getIdentity()
    )) ?>
</div>
<div>
    <button onclick="parent.Smoothbox.close();" ><?php echo $this->translate('Close') ?></button>
</div>
