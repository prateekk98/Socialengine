<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FieldYouTubeChannel.php 9747 2017-11-10 02:08:08Z john $
 * @author     Donna
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Donna
 */
class Fields_View_Helper_FieldYoutubechannel extends Fields_View_Helper_FieldAbstract
{
  public function fieldYoutubechannel($subject, $field, $value)
  {
   $regex = '/^((http(s|):\/\/|)(www\.|)|)youtube\.com\/channel\//i';
    
    $username = preg_replace($regex, '', trim($value->value));
    $ytcUrl = 'https://www.youtube.com/channel/' .  $username;
    
    return $this->view->htmlLink($ytcUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
    
  }
}
