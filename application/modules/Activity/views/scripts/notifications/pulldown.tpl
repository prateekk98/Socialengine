<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: pulldown.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>

<?php foreach( $this->notifications as $notification ): ?>
  <li <?php if( !$notification->read ): ?> class="notifications_unread"<?php endif; ?> id="notifications_<?php echo $notification->getIdentity();?>" value="<?php echo $notification->getIdentity();?>">
     <div class="notification_item_photo">
     <?php $user = Engine_Api::_()->getItem('user', $notification->subject_id);?>
     <?php if($notification->getContentObject() && ($notification->getContentObject() instanceof Core_Model_Item_Abstract)): ?>
        <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon',$notification->getContentObject()->getTitle(),array("class"=>"notification_subject_icon"))) ?>
  	 <?php endif; ?>
     </div>
    <div class="notification_item_general notification_item_content notification_type_<?php echo $notification->type ?>">
      <?php echo $notification->__toString() ?>
    </div>
    <div class="notifications_item_delete">
    <a href="javascript:void(0);" class="notifications_delete_show"><i class="fa fa-ellipsis-h"></i></a>
      <div class="notifications_delete_dropdown" id="notifications_delete_dropdown" style="display:none;">
        <a id="remove_notification_update" href="javascript:void(0);" onclick="removenotification('<?php echo $notification->getIdentity(); ?>');"><i class="far fa-times-circle"></i><?php echo $this->translate("Remove this Notification"); ?></a>
       </div>
    </div> 
  </li>
<?php endforeach; ?>
<script>
scriptJquery('.notifications_delete_show').on('click', function(event){
	if(scriptJquery(this).hasClass('showdropdown')){
		scriptJquery(this).removeClass('showdropdown');
	}else{
		scriptJquery('.notifications_delete_show').removeClass('showdropdown');
		scriptJquery(this).addClass('showdropdown');
	}
		return false;
});
</script>
