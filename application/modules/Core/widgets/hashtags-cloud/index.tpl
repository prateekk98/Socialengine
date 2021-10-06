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

<ul>
  <?php
  $url = $this->url(array(),'core_hashtags','true')."?search=";
  for ($i = 0; $i < count($this->hashtags); $i++):?>
    <li>
      <a href='<?php echo $url.urlencode($this->hashtags[$i]); ?>'>
        <?php echo $this->hashtags[$i]; ?>
      </a>
    </li>
  <?php endfor;?>
</ul>
