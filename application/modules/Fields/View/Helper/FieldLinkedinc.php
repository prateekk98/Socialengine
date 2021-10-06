<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FieldInstagram.php 9747 2017-11-10 02:08:08Z john $
 * @author     Donna
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     Donna
 */
class Fields_View_Helper_FieldLinkedinc extends Fields_View_Helper_FieldAbstract
{
  public function fieldLinkedinc($subject, $field, $value)
  {
   $regex = '/^((http(s|):\/\/|)(www\.|)|)linkedin\.com\/company\//i';
    
    $username = preg_replace($regex, '', trim($value->value));
    $licUrl = 'https://www.linkedin.com/company/' .  $username;
    
    return $this->view->htmlLink($licUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
    
  }
}
