<?php
/**
* SocialEngine
*
* @category   Application_Core
* @package    Core
* @copyright  Copyright 2006-2020 Webligo Developments
* @license    http://www.socialengine.com/license/
* @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
* @author     Jung
*/
?>
<style>
	#global_page_core-index-index .layout_middle > .layout_core_landing_page_banner{
		height:<?php echo $this->height; ?>px;
	}
</style>

<div class="core_landingpage_banner" style="height:<?php echo $this->height; ?>px">
  <section style="height:<?php echo $this->height; ?>px;background-image: url(<?php echo $this->banner->getPhotoUrl()?>)">
    <div>
      <h1>
        <?php echo $this->translate($this->banner->getTitle()) ?>
      </h1>
      <?php if( $this->banner->getDescription() ): ?>
        <article>
          <?php echo $this->translate($this->banner->getDescription()) ?>
        </article>
      <?php endif; ?>
      <?php if( $this->banner->getCTALabel() ): ?>
        <a href="<?php echo $this->banner->getCTAHref() ?>">
          <?php echo $this->translate($this->banner->getCTALabel()) ?>
        </a>
      <?php endif; ?>
    </div>
  </section>
</div>
