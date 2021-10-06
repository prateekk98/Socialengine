<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormDuration.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormFloodControl extends Zend_View_Helper_FormElement
{
  public function formFloodControl($name, $value = null, $attribs = null,
      $options = null, $listsep = " ")
  {
    // Process
    if( is_string($value) ) {
      if( preg_match('/^\d+\s+\w+$/', $value, $matches) ) {
        $value = array($matches[1], $matches[2]);
      } else {
        $value = array(null, null);
      }
    }
    
    if( is_array($value) ) {
      if( count($value) != 2 || !is_numeric($value[0]) || !is_string($value[1]) ) {
        $value = array(null, null);
      } else {
        $value[1] = rtrim($value[1], 's'); // Remove s
      }
    } else {
      $value = array(null, null);
    }

    return $this->view->formText($name . '[]', $value[0], array(
        'id' => $name . '-text',
        'disable' => !empty($attribs['disable']),
      ))
      . $listsep
      . $this->view->formSelect($name . '[]', $value[1], array(
          'multiple' => false,
          'id' => $name . '-select',
          'disable' => !empty($attribs['disable']),
        ), $options);
  }
}
