<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Fieldflickr.php 9747 2017-11-10 02:08:08Z john $
 * @author     Donna
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Donna
 */
class Fields_View_Helper_FieldFlickr extends Fields_View_Helper_FieldAbstract
{
  public function fieldFlickr($subject, $field, $value)
  {
   $regex = '/^((http(s|):\/\/|)(www\.|)|)flickr\.com\/people\//i';
    
    $username = preg_replace($regex, '', trim($value->value));
    $flickUrl = 'https://www.flickr.com/people/' .  $username;
    
    return $this->view->htmlLink($flickUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
    
  }
}
