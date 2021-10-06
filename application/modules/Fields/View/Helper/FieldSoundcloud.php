<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FieldSoundcloud.php 9747 2017-11-10 02:08:08Z john $
 * @author     Donna
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Donna
 */
class Fields_View_Helper_FieldSoundcloud extends Fields_View_Helper_FieldAbstract
{
  public function fieldSoundcloud($subject, $field, $value)
  {
   $regex = '/^((http(s|):\/\/|)(www\.|)|)soundcloud\.com\//i';
    
    $username = preg_replace($regex, '', trim($value->value));
    $scUrl = 'https://soundcloud.com/' .  $username;
    
    return $this->view->htmlLink($scUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
    
  }
}