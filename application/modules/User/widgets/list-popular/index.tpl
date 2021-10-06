<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: index.tpl 10167 2014-04-15 19:18:29Z lucas $
 * @author     John
 */
?>

<div class="generic_list_wrapper">
    <ul class="generic_list_widget">
      <?php foreach( $this->paginator as $user ): ?>
        <li>
          <?php echo $this->htmlLink($user->getHref(), $this->itemPhoto($user, 'thumb.icon', $user->getTitle()), array('class' => 'popularmembers_thumb')) ?>
          <div class='popularmembers_info'>
            <div class='popularmembers_name'>
              <?php echo $this->htmlLink($user->getHref(), $user->getTitle()) ?>
            </div>
            <div class='popularmembers_friends'>
              <?php echo $this->translate(array('%s friend', '%s friends', $user->member_count),$this->locale()->toNumber($user->member_count)) ?>
            </div>
          </div>
        </li>
      <?php endforeach; ?>
    </ul>
    
    <?php if( $this->paginator->getPages()->pageCount > 1 ): ?>
      <?php echo $this->partial('_widgetLinks.tpl', 'core', array(
        'url' => $this->url(array('action' => 'browse'), 'user_general', true),
        'param' => array('orderby' => 'member_count')
        )); ?>
    <?php endif; ?>
</div>
