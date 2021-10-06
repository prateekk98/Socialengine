<?php
/**
 * SocialEngine
 *
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manifest.php 10064 2013-06-19 21:30:42Z john $
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'core',
    'name' => 'base',
    'version' => '5.7.0',
    'revision' => '$Revision: 10064 $',
    'path' => '/',
    'repository' => 'socialengine.com',
    'title' => 'Base',
    'description' => 'Base',
    'author' => 'Webligo Developments',
    'actions' => array(
      'install',
      'upgrade',
      'refresh',
    ),
    'files' => array(
      '.htaccess',
      'crossdomain.xml',
      'README.html',
      'index.php',
        'boot.php',
      'robots.txt',
      'rpx_xdcomm.html',
      'xd_receiver.htm',
      'application/.htaccess',
      'application/cli.php',
      'application/css.php',
      'application/index.php',
      'application/lite.php',
      'application/maintenance.html',
      'application/offline.html',
      'application/libraries/index.html',
      'application/modules/index.html',
      'application/packages/index.html',
      'application/plugins/index.html',
      'application/themes/index.html',
      'application/themes/.htaccess',
      'application/widgets/index.html',
      'externals/.htaccess',
      'externals/index.html',
      'public/.htaccess',
      'public/admin/index.html',
      'public/temporary/index.html',
      'public/user/index.html',
      'temporary/.htaccess',
      'temporary/backup/index.html',
      'temporary/cache/index.html',
      'temporary/log/index.html',
      'temporary/log/scaffold/index.html',
      'temporary/package/index.html',
      'temporary/package/archives/index.html',
      'temporary/package/manifests/index.html',
      'temporary/package/packages/index.html',
      'temporary/package/repositories/index.html',
      'temporary/package/sdk/index.html',
      'temporary/scaffold/index.html',
      'temporary/session/index.html',
    ),
    'directories' => array(
        'application/settings',
        'vendor',
        'boot',
        'apps'
    ),
    'permissions' => array(
      array(
        'path' => 'application/languages',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'application/packages',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'application/themes',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'application/settings',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'public',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
      array(
        'path' => 'temporary',
        'mode' => 0777,
        'inclusive' => true,
        'recursive' => true,
      ),
    ),
    'tests' => array(
      // PHP Version
      array(
        'type' => 'PhpVersion',
        'name' => 'PHP 7',
        'minVersion' => '7.2',
      ),
      // MySQL Adapters are in module-core
      // Apache Modules
      array(
        'type' => 'ApacheModule',
        'name' => 'mod_rewrite',
        'module' => 'mod_rewrite',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noModule' => 'mod_rewrite does not appear to be available. This is OK, but it might prevent you from having search engine-friendly URLs.',
        ),
      ),
      // PHP Config
      array(
        'type' => 'PhpConfig',
        'name' => 'PHP Safe Mode OFF',
        'directive' => 'safe_mode',
        'comparisonMethod' => 'l',
        'comparisonValue' => 1,
        'messages' => array(
          'badValue' => 'PHP Safe Mode is currently ON - please turn it off and try again.',
        ),
      ),
      array(
        'type' => 'PhpConfig',
        'name' => 'PHP Register Globals OFF',
        'directive' => 'register_globals',
        'comparisonMethod' => 'l',
        'comparisonValue' => 1,
        'messages' => array(
          'badValue' => 'PHP Register Globals is currently ON - please turn it off and try again.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'GD',
        'extension' => 'gd',
        'messages' => array(
          'noExtension' => 'The GD Image Library is required for resizing images.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Imagick',
        'extension' => 'imagick',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'For optimal results with GIF images, we recommend adding the Imagick extension.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Iconv',
        'extension' => 'iconv',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The Iconv library is recommended for languages other than English.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Multi-byte String',
        'extension' => 'mbstring',
        'messages' => array(
          'noExtension' => 'The Multi-byte String (mbstring) library is required.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'PCRE',
        'extension' => 'pcre',
        'messages' => array(
          'noExtension' => 'The Perl-Compatible Regular Expressions extension is required.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Curl',
        'extension' => 'curl',
        'messages' => array(
          'noExtension' => 'The Curl extension is required.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'Session',
        'extension' => 'session',
        'messages' => array(
          'noExtension' => 'Session support is required.',
        ),
      ),
      array(
        'type' => 'PhpExtension',
        'name' => 'DOM',
        'extension' => 'dom',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The DOM (Document Object Model) extension is required for RSS feed parsing and link attachments.',
        ),
      ),
      // PHP Exif
      array(
        'type' => 'PhpExtension',
        'name' => 'Exif',
        'extension' => 'exif',
        'defaultErrorType' =>  1, // Engine_Sanity::ERROR_NOTICE,
        'messages' => array(
          'noExtension' => 'The Exif extension is required for enabling correct rotation of photos uploaded via mobile devices.',
        ),
      ),
      // File Permissions
      array(
        'type' => 'FilePermission',
        'name' => 'Public Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'public',
        'value' => 7,
        'recursive' => true,
        'ignoreFiles' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the public/ directory',
        ),
      ),
      array(
        'type' => 'Multi',
        'name' => 'Temp Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'allForOne' => false,
        'breakOnFailure' => true,
        'messages' => array(
          'oneTestFailed' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the temporary/ directory',
          'someTestsFailed' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the temporary/ directory',
          'allTestsFailed' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the temporary/ directory',
        ),
        'tests' => array(
          array(
            'type' => 'FilePermission',
            'path' => 'temporary',
            'value' => 7,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/cache',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/log',
            'recursive' => true,
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package/archives',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package/packages',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/package/repositories',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/scaffold',
            'value' => 7,
            'ignoreMissing' => true,
          ),
          array(
            'type' => 'FilePermission',
            'path' => 'temporary/session',
            'value' => 7,
            'ignoreMissing' => true,
          ),
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Packages Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/packages',
        'value' => 7,
        'recursive' => true,
        'ignoreFiles' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/packages/ directory',
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Settings Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/settings',
        'value' => 7,
        'recursive' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/settings/ directory',
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Language Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/languages',
        'value' => 7,
        'recursive' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/languages/ directory',
        ),
      ),
      array(
        'type' => 'FilePermission',
        'name' => 'Theme Directory Permissions',
        'defaultErrorType' => 1, // Engine_Sanity::ERROR_NOTICE,
        'path' => 'application/themes',
        'value' => 7,
        'recursive' => true,
        'messages' => array(
          'insufficientPermissions' => 'Please log in over FTP and set CHMOD 0777 (recursive) on the application/themes/ directory',
        ),
      ),
    ),
  ),
); ?>
