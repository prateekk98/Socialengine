<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserFields.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserFields extends Install_Import_Phpfox_AbstractFields
{

  protected $_toTableTruncate = false;
  protected $_fromResourceType = 'profile';
  protected $_fromAlternateResourceType = 'user';
  protected $_toResourceType = 'user';
  protected $_useProfileType = true;
  protected $_priority = 5000;

  protected function _run()
  {
    //$this->_message('Not implemented', 2);
  }
}
