<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ZipCode.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Element_ZipCode extends Engine_Form_Element_Text
{
  public function init()
  {
    $this->addValidator('Regex', true, array(
      '/^([0-9]{3,6})$|^([0-9]{5}-[0-9]{4})$|^([a-zA-Z0-9]{1,2}[0-9]{1,4})$|^([a-zA-Z0-9]{2,4}\s[a-zA-Z0-9]{2,4})$|^([a-zA-Z0-9]{1,3}-[a-zA-Z0-9]{2,4})$/'
      ));
    // Fix messages
    $this->getValidator('Regex')->setMessage("'%value%' is not a valid zip code.", 'regexNotMatch');
  }
}
