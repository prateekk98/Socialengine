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
<?php $imageArray = array('1' => 'login-signup.png', '2' => 'post-content.png', '3' => 'responsive.png', '4' => 'flexible.png'); ?>
<?php $allParams = $this->allParams; ?>
<div class="core_landingpage_features">
  <section>
    <?php for($i=1;$i<=4;$i++) { ?>
      <?php if(!empty($allParams['fe'.$i.'img']) || !empty($allParams['fe'.$i.'heading']) || !empty($allParams['fe'.$i.'description'])) { ?>
        <div>
          <?php $image = !empty($allParams['fe'.$i.'img']) ? Engine_Api::_()->core()->getFileUrl($allParams['fe'.$i.'img']) : 'application/modules/Core/externals/images/feature-icons/'.$imageArray[$i]; ?>
          <img src="<?php echo Engine_Api::_()->core()->getFileUrl($image); ?>" />
          <?php if(!empty($allParams['fe'.$i.'heading'])) { ?>
            <h3><?php echo $this->translate($allParams['fe'.$i.'heading']); ?></h3>
          <?php } ?>
          <?php if(!empty($allParams['fe'.$i.'description'])) { ?>
            <p><?php echo $this->translate($allParams['fe'.$i.'description']); ?></p>
          <?php } ?>
        </div>
      <?php } ?>
    <?php } ?>
  </section>
</div>
