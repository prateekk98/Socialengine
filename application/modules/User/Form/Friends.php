<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Search.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Form_Friends extends Engine_Form
{
  protected $_user;

  public function setUser(Core_Model_Item_Abstract $user)
  {
    $this->_user = $user;
    return $this;
  }

  public function init()
  {
    $this->addElement('Hash', 'token_' . $this->_user->getGuid());
  }
}
