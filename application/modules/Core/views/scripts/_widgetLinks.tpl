<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _composeLink.tpl 10245 2017-01-02 18:08:24Z lucas $
 * @author     John
 */
?>
<?php $href = $this->url; ?>
<?php if( isset($this->param) ): ?>
  <?php $href .= "?" . urldecode(http_build_query($this->param)); ?>
<?php endif;?>
<a class="viewlink" href="<?php echo $href ?>"><?php echo $this->translate('View All') ?><i class="fa-angle-double-right fa"></i></a>
