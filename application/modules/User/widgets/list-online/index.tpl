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
<?php if (strpos($this->title, '%s') !== false) { ?>
  <h3><?php echo $this->translate(array($this->title, $this->title, $this->paginator->getTotalItemCount()), $this->locale()->toNumber($this->paginator->getTotalItemCount())) ?></h3>
<?php } else { ?>
  <h3><?php echo $this->translate($this->title); ?></h3>
<?php } ?>
<div>
  <?php foreach( $this->paginator as $user ): ?>
    <div class='whosonline_thumb'>
      <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon', $user->getTitle()), array('title'=>$user->getTitle())) ?>
    </div>
  <?php endforeach; ?>
  
  <?php if( $this->guestCount ): ?>
    <div class="online_guests">
      <?php echo $this->translate(array('%s guest online', '%s guests online', $this->guestCount),
          $this->locale()->toNumber($this->guestCount)) ?>
    </div>
  <?php endif ?>
</div>
