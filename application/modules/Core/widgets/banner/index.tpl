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
<div class="core_banner_<?php echo $this->banner->name ?>" <?php if( $this->banner->getPhotoUrl() ): ?> style="background-image: url(<?php echo $this->banner->getPhotoUrl()?>)" <?php endif;?>>
  <section>
    <div>
      <?php if( !empty($this->banner->params['partial']) ): ?>
        <?php echo $this->view->partial($this->banner->params['partial'], array(
          'banner' => $this->banner
        )); ?>
      <?php else: ?>
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
      <?php endif; ?>
    </div>
  </section>
</div>
