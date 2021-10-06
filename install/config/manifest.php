<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */

return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'core',
    'name' => 'install',
    'version' => '5.7.0',
    'revision' => '$Revision: 10271 $',
    'path' => '/',
    'repository' => 'socialengine.com',
    'title' => 'Package Manager',
    'description' => 'Package Manager',
    'author' => 'Webligo Developments',
    'dependencies' => array(
      array(
        'type' => 'library',
        'name' => 'engine',
      //'excludeExcept' => true,
        'required' => true,
        'minVersion' => '4.10.5',
      ),
    ),
    'actions' => array(
      'install',
      'upgrade',
      'refresh',
    ),
    'directories' => array(
      'install',
    ),
    'permissions' => array(
      array(
        'path' => 'install/config',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'temporary/package',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
    ),
    'tests' => array(
      /*
      array(
        'type' => 'FilePermission',
        'name' => 'Install Permissions',
        'path' => 'install/config/auth.php',
        'value' => 7,
        'recursive' => false,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the install/config/ directory',
        ),
      ),
      */
    ),
  ),
); ?>
