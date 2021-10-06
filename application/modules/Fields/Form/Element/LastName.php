<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: LastName.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Element_LastName extends Engine_Form_Element_Text
{
	public function init()
  	{
	   	$validator = new Engine_Validate_Callback(array($this, 'validateAge'));
      	$validator->setMessage('Special Characters are not allowed in the last name.');
      	$this->addValidator($validator);

  	}
	public function validateAge($value)
	{
	    if ( preg_match("/[^a-zA-Z0-9]+/",$value) ){
	   		return false;
	    }
	    return true;
	}
}
