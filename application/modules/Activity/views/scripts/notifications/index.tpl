<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<script type="text/javascript">
  var notificationPageCount = <?php echo sprintf('%d', $this->notifications->count()); ?>;
  var notificationPage = <?php echo sprintf('%d', $this->notifications->getCurrentPageNumber()); ?>;
  var loadMoreNotifications = function() {
    notificationPage++;
    scriptJquery.ajax({
      url : en4.core.baseUrl + 'activity/notifications/pulldown',
      dataType : 'html',
      method : 'post',
      data : {
        format : 'html',
        page : notificationPage
      },
      complete : function(responseHTML) {
        scriptJquery('#notifications_loading_main').css('display', 'none');
        if( '' != responseHTML.trim() && notificationPageCount > notificationPage ) {
          scriptJquery('#notifications_viewmore').css('display', '');
        }
        scriptJquery('#notifications_main')[0].innerHTML += responseHTML;
      }
    });
  };
  en4.core.runonce.add(function(){
    if(scriptJquery('#notifications_viewmore_link').length){
      scriptJquery('#notifications_viewmore_link').on('click', function() {
        scriptJquery('#notifications_viewmore').css('display', 'none');
        scriptJquery('#notifications_loading_main').css('display', '');
        loadMoreNotifications();
      });
    }

    if(scriptJquery('#notifications_markread_link_main')){
      scriptJquery('#notifications_markread_link_main').on('click', function() {
        scriptJquery('#notifications_markread_main').css('display', 'none');
        en4.activity.hideNotifications('<?php echo $this->translate("0 Updates");?>');
      });
    }
    
    scriptJquery('#notifications_main').on('click', function(event){
        event.preventDefault(); //Prevents the browser from following the link.
        if(event.target.id != 'notification_id') {
          var current_link = event.target;
          var notification_li = scriptJquery(current_link).parents('li').eq(0);
  //         if(current_link.hasClass("notification_subject_icon")){
  //           current_link = current_link.parents("a").eq(0).attr('href');
  //         } else{
  //           current_link = current_link.attr('href');
  //         }
          if(current_link){
            en4.core.request.send(scriptJquery.ajax({
              url : en4.core.baseUrl + 'activity/notifications/markread',
              dataType : 'html',
              method : 'json',
              data : {
                format     : 'json',
                'actionid' : notification_li.val()
              },
              onSuccess : window.location = current_link
            }));
          }
        }
    });

  });
</script>

<div class='layout_middle'>
  <div class='generic_layout_container'>
    <div class='notifications_layout'>
      <div class='notifications_leftside'>
        <h3 class="sep"> 
          <span><?php echo $this->translate("Recent Updates") ?></span> 
        </h3>
        <ul class='notifications' id="notifications_main">
          <?php if( $this->notifications->getTotalItemCount() > 0 ): ?>
          <?php
                foreach( $this->notifications as $notification ):
                ob_start();
                try { ?>
          <li<?php if( !$notification->read ): ?> class="notifications_unread"<?php $this->hasunread = true; ?> <?php endif; ?> value="<?php echo $notification->getIdentity();?>">
            <?php // removed onclick event onclick="javascript:en4.activity.markRead($notification->getIdentity() ?>
            <div class="notification_item_photo">
              <?php if($notification->getContentObject() && ($notification->getContentObject() instanceof Core_Model_Item_Abstract)): ?>
              <?php echo $this->htmlLink($notification->getContentObject()->getHref(),$this->itemPhoto($notification->getContentObject(), 'thumb.icon',$notification->getContentObject()->getTitle(),array("class"=>"notification_subject_icon"))); ?>
              <?php endif; ?>
            </div>
            <div class="notification_item_general notification_item_content notification_type_<?php echo $notification->type ?>"> <?php echo $notification->__toString(), $this->translate(' Posted %1$s', $this->timestamp($notification->date)) ?> 
            <div class="notification_item_general_delete"><a id="notification_id" class="smoothbox delete_noti" href="<?php echo $this->url(array('action' => 'delete-notification', 'notification_id' => $notification->getIdentity()), 'recent_activity', true) ?>"><i class="fas fa-trash-alt"></i><?php echo $this->translate("Delete"); ?></a></div>
            </div>
          </li>
          <?php
                } catch( Exception $e ) {
                  ob_end_clean();
                  if( APPLICATION_ENV === 'development' ) {
                    echo $e->__toString();
                  }
                  continue;
                }
                ob_end_flush();
                endforeach;
              ?>
          <?php else: ?>
          <li> <?php echo $this->translate("You have no notifications.") ?> </li>
          <?php endif; ?>
        </ul>
        <div class="notifications_options">
          <?php if( $this->hasunread ): ?>
          <div class="notifications_markread" id="notifications_markread_main"> <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Mark All Read'), array(
                  'id' => 'notifications_markread_link_main',
                  'class' => 'buttonlink notifications_markread_link'
                )) ?> </div>
          <?php endif; ?>
          <?php if( $this->notifications->getTotalItemCount() > 1 ): ?>
          <div class="notifications_viewmore" id="notifications_viewmore"> <?php echo $this->htmlLink('javascript:void(0);', $this->translate('View More'), array(
                  'id' => 'notifications_viewmore_link',
                  'class' => 'buttonlink notifications_viewmore_link icon_viewmore'
                )) ?> </div>
          <?php endif; ?>
          <div class="notifications_viewmore" id="notifications_loading_main" style="display: none;"> <i class="fa fa-spinner fa-spin" style=' margin-right: 5px;'></i> <?php echo $this->translate("Loading ...") ?> </div>
          <?php if( $this->notifications->getTotalItemCount() > 0 ): ?>
          <div class="notifications_delete" id="notifications_delete"><a href="<?php echo $this->url(array('action' => 'delete-notifications'), 'recent_activity', true); ?>" id="notifications_delete_link" class="smoothbox buttonlink notifications_delete_link"><?php echo $this->translate("Delete All"); ?></a></div>
          <?php endif; ?>
        </div>
      </div>
      <div class='notifications_rightside'>
        <h3 class="sep">
          <?php  $itemCount = $this->requests->getTotalItemCount(); ?>
          <span><?php echo $this->translate(array("My Request (%d)","My Requests (%d)", $itemCount), $itemCount) ?></span> </h3>
        <ul class='requests'>
          <?php if( $this->requests->getTotalItemCount() > 0 ): ?>
          <?php foreach( $this->requests as $notification ): ?>
          <?php
                try {
                  $parts = explode('.', $notification->getTypeInfo()->handler);
                  echo $this->action($parts[2], $parts[1], $parts[0], array('notification' => $notification));
                } catch( Exception $e ) {
                  if( APPLICATION_ENV === 'development' ) {
                    echo $e->__toString();
                  }
                  continue;
                }
              ?>
          <?php endforeach; ?>
          <?php else: ?>
          <li> <?php echo $this->translate("You have no requests.") ?> </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>
