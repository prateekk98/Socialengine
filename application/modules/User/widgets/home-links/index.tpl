<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php if( $this->showPhoto == 1): ?>
<div class="home-links-user">
    <div class="image">
        <?php echo $this->htmlLink($this->viewer()->getHref(), $this->itemPhoto($this->viewer(), 'thumb.icon')) ?>
    </div>
    <div class="user">
        <?php echo $this->htmlLink($this->viewer()->getHref(), $this->viewer()->getTitle()); ?>
    </div>
</div>
<?php endif; ?>

<div class="quicklinks">
  <?php
    echo $this->navigation()
      ->menu()
      ->setContainer($this->navigation)
      ->setPartial(array('_navIcons.tpl', 'core'))
      ->render()
  ?>
</div>
