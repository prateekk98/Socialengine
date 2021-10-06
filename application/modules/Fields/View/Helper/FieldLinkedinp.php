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
class Fields_View_Helper_FieldLinkedinp extends Fields_View_Helper_FieldAbstract
{
  public function fieldLinkedinp($subject, $field, $value)
  {
   $regex = '/^((http(s|):\/\/|)(www\.|)|)linkedin\.com\/in\//i';
    
    $username = preg_replace($regex, '', trim($value->value));
    $lipUrl = 'https://www.linkedin.com/in/' .  $username;
    
    return $this->view->htmlLink($lipUrl, $value->value, array(
      'target' => '_blank',
      'ref' => 'nofollow',
    ));
    
  }
}
