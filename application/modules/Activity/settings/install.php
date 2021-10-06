<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

/**
 * @category   Application_Extensions
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Installer extends Engine_Package_Installer_Module
{
  protected $_dropColumnsOnPreInstall = array(
    '4.9.0' => array(
      'engine4_activity_actiontypes' => array('editable'),
      'engine4_activity_actions' => array('modified_date')
    )
  );
}
