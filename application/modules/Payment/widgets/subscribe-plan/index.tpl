<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<div class="payment_subscribe_plan">
   <ul>
      <li class="plan_box">
	    <div class="_cont">
      <?php if(isset($this->planTitleActive)): ?>
        <div class="_title"><?php echo $this->translate($this->currentPackage->title); ?></div>
        <div class="_desc"><?php echo $this->currentPackage->getPackageDescription(); ?></div>
      <?php endif; ?>
		  <ul>
		  <?php if($this->currentPackage->hasDuration() && isset($this->expiryDateActive)): ?>
		    <li><?php echo $this->translate('Plan Expiry Date: '); ?><b><?php echo date('Y-m-d',strtotime($this->currentSubscription->expiration_date)); ?></b></li>
      <?php elseif($this->nextPaymentActive && !$this->currentPackage->isOneTime()): ?>
           <li><?php echo $this->translate('Next Payment Date: '); ?><b><?php echo date('Y-m-d',strtotime($this->currentSubscription->expiration_date)); ?></b></li>
      <?php endif; ?>
      <?php
        $date1 = date_create(date('Y-m-d H:i:s'));
        $date2 = date_create($this->currentSubscription->expiration_date);
        $diff = date_diff($date1,$date2);
      ?>
      <?php if( !empty($this->level->type) && isset($this->currentMemberActive)): ?>
        <li><?php echo $this->translate('Member Level: '); ?><b><?php echo $this->translate($this->level->getTitle()); ?></b></li>
      <?php endif; ?>
      <?php if($this->currentPackage->hasDuration() && isset($this->daysleftActive)): ?>
        <?php $text = $this->translate(array('Subscription Day Left: <b>%s Day</b>', 'Subscription Days Left: <b>%s Days</b>', $diff->format("%a")), $this->locale()->toNumber($diff->format("%a"))); ?>
        <li><?php echo $text;  ?></li>
			<?php endif; ?>
		  </ul>
		</div>
		<?php if($this->isGatewayEnabled && $this->currentPackage->hasDuration() && $this->currentPackage->isOneTime() && $this->paymentButton): ?>
      <form method="get" action="<?php echo $this->escape($this->url(array('action' => 'process','controller'=>'subscription','module'=>'payment'),'default')) ?>"
          class="global_form" enctype="application/x-www-form-urlencoded">
        <input type="hidden" name="gateway_id" id="gateway_id" value="<?php echo $this->currentSubscription->gateway_id; ?>" />
        <input type="hidden" name="subscription_id" id="subscription_id" value="<?php echo $this->currentSubscription->subscription_id; ?>" />
        <button type="submit" name="execute">
          <?php echo $this->translate('Make Payment'); ?>
        </button>
      </form>
    <?php endif; ?>
	  </li>
   </ul>

</div>
