<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<?php
  $request = Zend_Controller_Front::getInstance()->getRequest();
  $controllerName = $request->getControllerName();
  $actionName = $request->getActionName();
  $showSearch = true;
  if($controllerName == 'signup') {
    $showSearch = false;
  } else if($actionName == 'login') {
    $showSearch = false;
  }
  $themes = Engine_Api::_()->getDbtable('themes', 'core')->fetchAll();
  $activeTheme = $themes->getRowMatching('active', 1);
  $manifest = APPLICATION_PATH . '/application/themes/' . $activeTheme->name . '/manifest.php';
  $themeManifest = null;
  if( file_exists($manifest) ) {
    $themeManifest = require($manifest);
  }
?>
<div id='core_menu_mini_menu'>
  <ul>
    <?php foreach( $this->navigation as $item ): ?>
      <?php
        $linkTitle = '';
        $subclass = '';
          $linkTitle = $this->translate(strip_tags($item->getLabel()));
          if( $this->showIcons ) {
            $subclass = ' show_icons';
          }
        $className = explode(' ', $item->class);
        $class = !empty($item->class) ? $item->class . $subclass : null;
      ?>
      <?php if(end($className) == 'core_mini_settings') { ?>
        <li>
          <a href="javascript:void(0);" class="<?php echo $class; ?>" <?php if( $item->get('target') ): ?> target='<?php echo $item->get('target') ?>' <?php endif; ?> title="<?php echo $linkTitle; ?>" alt="<?php echo ( !empty($item->alt) ? $item->alt : null ); ?>" id="minimenu_settings" onclick="showSettingsBox();"><i class="<?php echo $item->get('icon') ? $item->get('icon') : 'far fa-star' ?>"></i><?php echo $linkTitle; ?></a>
          <div class="core_settings_dropdown" id="minimenu_settings_content">
            <?php echo $this->navigation()->menu()->setContainer($this->settingNavigation)->render();?>
          </div>
        </li>
      <?php } else if(end($className) == 'core_mini_messages') { ?>
      <li class="core_mini_messages">
        <?php if($this->message_count && $this->showIcons) { ?>
          <span id="minimenu_message_count_bubble" class="minimenu_message_count_bubble <?php echo $subclass ?>"><?php echo $this->message_count; 
        ?></span>
        <?php } ?>
        <a href="javascript:void(0);" class="<?php echo $class; ?>" <?php if( $item->get('target') ): ?> target='<?php echo $item->get('target') ?>' <?php endif; ?> title="<?php echo $linkTitle; ?>" alt="<?php echo ( !empty($item->alt) ? $item->alt : null ); ?>" id="minimenu_message" onclick="showMessageBox();"><i class="<?php echo $item->get('icon') ? $item->get('icon') : 'far fa-star' ?>"></i><?php echo $linkTitle; ?></a>
        <div class="pulldown_contents_wrapper" id="pulldown_message" style="display:none;">
          <div class="pulldown_contents">
            <div class="core_messages_pulldown_header">
              <?php echo $this->translate("Messages "); ?><a class="icon_message_new righticon fa fa-plus" href="messages/compose" title="<?php echo $this->translate('Compose New Message'); ?>"></a>
            </div>
            <ul class="messages_menu" id="messages_menu">
              <li class='clearfix pulldown_content_list_highlighted'>
                <img src='<?php echo $this->layout()->staticBaseUrl ?>application/modules/Core/externals/images/loading.gif' alt="<?php echo $this->translate('Loading'); ?>" />
              </li>
            </ul>
          </div>
          <div class="pulldown_options">
            <a id="messages_viewall_link" href="<?php echo $this->url(array('action' => 'inbox'), 'messages_general', true) ?>"><?php echo $this->translate("View All Messages") ?></a>
            <a href="javascript:void(0);" id="messages_markread_link" onclick="markAllReadMessages();"><?php echo $this->translate("Mark All Read") ?></a>
          </div>
        </div>
      </li>
      <?php } else { ?>
        <?php $isauth = in_array(end($className), array('core_mini_auth','core_mini_signup')); ?>
        <li>
          <a href='<?php echo $item->getHref() ?>' class="<?php echo $class  ?>"
            <?php if( $item->get('target') ): ?> target='<?php echo $item->get('target') ?>' <?php endif; ?> title="<?php echo $linkTitle; ?>" alt="<?php echo ( !empty($item->alt) ? $item->alt : null ); ?>">
            
              <?php if(end($className) == 'core_mini_profile') { ?>
                <?php echo Zend_Registry::get('Zend_View')->itemPhoto($this->viewer, 'thumb.icon'); ?>
              <?php } else { ?>
                <?php if($this->showIcons) {  ?>
                  <i class="<?php echo $item->get('icon') ? $item->get('icon') : (!$isauth ? 'far fa-star' : '') ?>"></i>
                <?php } ?>
              <?php } ?>
            
            <span><?php echo $linkTitle; ?></span>
          </a>
          <!-- For displaying count bubble : START -->
          <?php
            $countText = filter_var($item->getLabel(), FILTER_SANITIZE_NUMBER_INT);
          ?>
          <?php if($this->showIcons && stripos($item->class, 'core_mini_update') !== false ) : ?>
            <span class="minimenu_update_count_bubble <?php echo $subclass ?>" id="update_count">
              <?php echo $countText; ?>
            </span>
          <?php elseif( stripos($item->class, 'core_mini_messages') !== false && !empty($countText) ) : ?>
            <span class="minimenu_message_count_bubble <?php echo $subclass ?>" id="message_count">
              <?php echo $countText; ?>
            </span>
          <?php endif; ?>
          <!-- For displaying count bubble : END -->
        </li>
      <?php } ?>
    <?php endforeach; ?>
    <?php if($this->search_check):?>
      <li id="global_search_form_container">
        <form id="global_search_form" action="<?php echo $this->url(array('controller' => 'search'), 'default', true) ?>" method="get">
          <input type='text' class='text suggested' name='query' id='global_search_field' size='20' maxlength='100' alt='<?php echo $this->translate('Search') ?>' />
        </form>
      </li>
    <?php endif;?>
  </ul>
</div>

<span  style="display: none;" class="updates_pulldown" id="core_mini_updates_pulldown">
  <div class="pulldown_contents_wrapper">
    <div class="pulldown_contents">
      <ul class="notifications_menu" id="notifications_menu">
        <div class="notifications_loading" id="notifications_loading">
          <i class="fa fa-spin fa-spinner" style='margin-right: 5px;' ></i>
          <?php echo $this->translate("Loading ...") ?>
        </div>
      </ul>
    </div>
    <div class="pulldown_options">
      <?php echo $this->htmlLink(array('route' => 'default', 'module' => 'activity', 'controller' => 'notifications'),
         $this->translate('View All Updates'),
         array('id' => 'notifications_viewall_link')) ?>
      <?php echo $this->htmlLink('javascript:void(0);', $this->translate('Mark All Read'), array(
        'id' => 'notifications_markread_link',
      )) ?>
    </div>
  </div>
</span>

<?php if(!empty($this->viewer->getIdentity())) { ?>

<script type='text/javascript'>
  scriptJquery(document).ready(function() {
    scriptJquery("body").on('click',function(event) {
      if(event.target.id != '' && event.target.id != 'updates_toggle' && event.target.id != 'minimenu_message' && event.target.id != 'minimenu_settings' && event.target.id != 'notifications_delete_show' && event.target.id != 'notifications_delete_icon') {
        if(scriptJquery(".updates_pulldown_active").length > 0)
          scriptJquery('.updates_pulldown_active').attr('class', 'updates_pulldown');

        if(scriptJquery("#pulldown_message").length && scriptJquery("#pulldown_message")[0].style.display == 'block')
          scriptJquery("#pulldown_message")[0].style.display = 'none';
          
        if(scriptJquery('#minimenu_settings_content').length && scriptJquery('#minimenu_settings_content')[0].style.display == 'block')
          scriptJquery('#minimenu_settings_content')[0].style.display = 'none';
      }
    });
  });
  
  function showSettingsBox() {
    if(scriptJquery(".updates_pulldown_active").length > 0)
      scriptJquery('.updates_pulldown_active').attr('class', 'updates_pulldown');
      
    if(scriptJquery('#pulldown_message') && scriptJquery('#pulldown_message')[0].style.display == 'block')
      document.getElementById('pulldown_message').style.display = 'none';

    if(scriptJquery('#minimenu_settings_content') && scriptJquery('#minimenu_settings_content')[0].style.display == 'block')
      document.getElementById('minimenu_settings_content').style.display = 'none';
    else
      document.getElementById('minimenu_settings_content').style.display = 'block';
  }

  function showMessageBox() {
    if(scriptJquery('#minimenu_settings_content').length && scriptJquery('#minimenu_settings_content')[0].style.display == 'block')
      scriptJquery('#minimenu_settings_content').style.display = 'none';
    if(scriptJquery(".updates_pulldown_active").length > 0)
      scriptJquery('.updates_pulldown_active').attr('class', 'updates_pulldown');
    if(scriptJquery('#pulldown_message').length && scriptJquery('#pulldown_message')[0].style.display == 'block')
      document.getElementById('pulldown_message').style.display = 'none';
    else
      document.getElementById('pulldown_message').style.display = 'block';
    showMessages();
  }

  function showMessages() {
    scriptJquery.ajax({
      url: en4.core.baseUrl + 'core/index/inbox',
      data: {
        format : 'html'
      },
      method:'post',
      dataType: 'html',
      success: function (responseHTML) {
         document.getElementById('messages_menu').innerHTML = responseHTML;
      },
      error: function (err) {
         console.log(err);
      }
    });
  }
  var notificationUpdater;
  en4.core.runonce.add(function(){
    if(scriptJquery('#notifications_markread_link').length){
      scriptJquery('#notifications_markread_link').on('click', function() {
        en4.activity.hideNotifications('<?php echo $this->string()->escapeJavascript($this->translate("0 Updates"));?>');
      });
    }
    <?php if ($this->updateSettings && $this->viewer->getIdentity()): ?>
    notificationUpdater = new NotificationUpdateHandler({
              'delay' : <?php echo $this->updateSettings;?>
            });
    notificationUpdater.start();
    window._notificationUpdater = notificationUpdater;
    <?php endif;?>
  });

  var updateElement = scriptJquery('#core_menu_mini_menu').find('.core_mini_update:first');
  if( updateElement.length ) {
    updateElement.attr('id', 'updates_toggle');
    scriptJquery('#core_mini_updates_pulldown').css('display', 'inline-block').appendTo(updateElement.parent().attr('id', 'core_menu_mini_menu_update'));

    updateElement.appendTo(scriptJquery('#core_mini_updates_pulldown'));

    scriptJquery('#core_mini_updates_pulldown').on('click', function(event) {
      if(event.target.id != 'notifications_delete_show' && event.target.id != 'notifications_delete_icon') {
        var element = scriptJquery(this);
        if(element.hasClass('updates_pulldown')) {
          element.removeClass('updates_pulldown');
          element.addClass('updates_pulldown_active');
          showNotifications();
        } else {
          element.addClass('updates_pulldown');
          element.removeClass('updates_pulldown_active');
        }
      }
    });
  }
  var showNotifications = function() {
    if(scriptJquery("#pulldown_message").length && scriptJquery("#pulldown_message")[0].style.display == 'block')
      document.getElementById("pulldown_message").style.display = 'none';
    en4.activity.updateNotifications();
    scriptJquery.ajax({
      url: en4.core.baseUrl + 'activity/notifications/pulldown',
      data:{
        format : 'html',
        page : 1
      },
      method:'post',
      dataType: 'html',
      success: function (responseHTML) {
        if( responseHTML ) {
          // hide loading icon
          if(scriptJquery('#notifications_loading').length) 
            scriptJquery('#notifications_loading').css('display', 'none');

          scriptJquery('#notifications_menu').html(responseHTML);
          scriptJquery('#notifications_menu').on('click', function(event){
            if(event.target.id != 'remove_notification_update') {
            event.preventDefault(); //Prevents the browser from following the link.
            
            var current_link = event.target;
            var notification_li = $(current_link).getParent('li');

            // if this is true, then the user clicked on the li element itself
            if( notification_li.id == 'core_menu_mini_menu_update' ) {
              notification_li = current_link;
            }

            var forward_link;
            if( current_link.get('href') ) {
              forward_link = current_link.get('href');
            } else{
              forward_link = $(current_link).getElements('a:last-child').get('href');
            }
            if( notification_li.hasClass('notifications_unread')){
              notification_li.removeClass('notifications_unread');
              scriptJquery.ajax({
                url: en4.core.baseUrl + 'activity/notifications/markread',
                data: {
                  format     : 'json',
                  actionid : notification_li.get('value')
                },
                method:'post',
                dataType: 'json',
                success: function (response) {
                  window.location = forward_link;
                },
                error: function (err) {
                  console.log(err);
                }
              });
            } else {
              window.location = forward_link;
            }
          }
          });
        } else {
          scriptJquery('#notifications_loading').html('<?php echo $this->string()->escapeJavascript($this->translate("You have no new updates."));?>');
        }
      },
      error: function () {
         
      }
    });
  };
  
  function removenotification(notification_id) {
    scriptJquery.ajax({
      url: en4.core.baseUrl + 'activity/notifications/remove-notification',
      data: {
        format : 'json',
        notification_id: notification_id,
      },
      method:'post',
      dataType: 'json',
      success: function (response) {
        var result = scriptJquery.parseJSON(response);
        if(result.status == 1) {
          scriptJquery('#notifications_'+notification_id).remove();
        }
      },
    });
  }
</script>
<?php } ?>
<?php if($showSearch) { ?>
  <script type='text/javascript'>
    en4.core.runonce.add(function() {
      // combining mini-menu and search widget if next to each other
      var menuElement = scriptJquery('#global_header').find('.layout_core_menu_mini:first');
      var nextWidget = menuElement.next();
      if( nextWidget.length && nextWidget.hasClass('layout_core_search_mini') ) {
        nextWidget.removeClass('generic_layout_container').prependTo(menuElement);
        return;
      }
      previousWidget = menuElement.previous();
      if( previousWidget.length && previousWidget.hasClass('layout_core_search_mini') ) {
        previousWidget.removeClass('generic_layout_container').prependTo(menuElement);
      }
    });
  </script>
<?php } ?>
