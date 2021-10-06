<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: _navFontIcons.tpl 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
?>
<ul class="navigation">
  <?php foreach( $this->container as $link ): ?>
    <li class="<?php echo $link->get('active') ? 'active' : '' ?>">
      <a href='<?php echo $link->getHref() ?>' class="<?php echo $link->getClass() ? ' ' . $link->getClass() : ''  ?>"
        <?php if( $link->get('target') ): ?> target='<?php echo $link->get('target') ?>' <?php endif; ?> >
        <i class="<?php echo $link->get('icon') ? $link->get('icon') : 'fa fa-star' ?>"></i>
        <span><?php echo $this->translate($link->getlabel()) ?></span>
      </a>
    </li>
  <?php endforeach; ?>
</ul>
