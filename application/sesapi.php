<?php

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('PS') || define('PS', PATH_SEPARATOR);
defined('_ENGINE') || define('_ENGINE', true);
defined('_ENGINE_REQUEST_START') || 
    define('_ENGINE_REQUEST_START', microtime(true));

defined('APPLICATION_PATH') || 
    define('APPLICATION_PATH',realpath(dirname(dirname(__FILE__))));
defined('APPLICATION_PATH_COR') || 
    define('APPLICATION_PATH_COR', realpath(dirname(__FILE__)));
defined('APPLICATION_PATH_EXT') || 
    define('APPLICATION_PATH_EXT', APPLICATION_PATH . DS . 'externals');
defined('APPLICATION_PATH_PUB') || 
    define('APPLICATION_PATH_PUB', APPLICATION_PATH . DS . 'public');
defined('APPLICATION_PATH_TMP') || 
    define('APPLICATION_PATH_TMP', APPLICATION_PATH . DS . 'temporary');

defined('APPLICATION_PATH_BTS') || 
    define('APPLICATION_PATH_BTS', APPLICATION_PATH_COR . DS . 'bootstraps');
defined('APPLICATION_PATH_LIB') || 
    define('APPLICATION_PATH_LIB', APPLICATION_PATH_COR . DS . 'libraries');
defined('APPLICATION_PATH_MOD') || 
    define('APPLICATION_PATH_MOD', APPLICATION_PATH_COR . DS . 'modules');
defined('APPLICATION_PATH_PLU') || 
    define('APPLICATION_PATH_PLU', APPLICATION_PATH_COR . DS . 'plugins');
defined('APPLICATION_PATH_SET') || 
    define('APPLICATION_PATH_SET', APPLICATION_PATH_COR . DS . 'settings');

// Setup required include paths; optimized for Zend usage. Most other includes
// will use an absolute path
set_include_path(
  APPLICATION_PATH_LIB . PS .
  APPLICATION_PATH_LIB . DS . 'PEAR' . PS .
  '.' // get_include_path()
);

defined('APPLICATION_NAME') || define('APPLICATION_NAME', 'Sesapi');
defined('_ENGINE_ADMIN_NEUTER_MODE') || define('_ENGINE_ADMIN_NEUTER_MODE', false);
defined('_ENGINE_NO_AUTH') || define('_ENGINE_NO_AUTH', false);
defined('_ENGINE_SSL') || define('_ENGINE_SSL', ((isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https') || (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on')));

  // maintenance mode
if (!defined('_ENGINE_R_MAINTENANCE') || _ENGINE_R_MAINTENANCE) {
    if (!empty($generalConfig['maintenance']['enabled']) && !empty($generalConfig['maintenance']['code'])) {
        $code = $generalConfig['maintenance']['code'];
        if (@$_REQUEST['en4_maint_code'] == $code || @$_COOKIE['en4_maint_code'] == $code) {
            if (@$_COOKIE['en4_maint_code'] !== $code) {
                setcookie('en4_maint_code', $code, time() + (86400 * 7), '/');
            }
            if (@$_REQUEST['en4_maint_code'] == $code){
              session_start();
              echo json_encode(array('message'=>"1","session_id"=>session_id()));die;
            }
        } else {         
            if (!empty($_REQUEST['en4_maint_code'])){
               echo json_encode(array('message'=>"",'error'=>1,'error_message'=>'Wrong Code!'));die;
            }
            echo json_encode(array('message'=>"",'error'=>1,'error_message'=>'maintenance_code_enable'));die;
        }
    }
}

// get general config
if( file_exists(APPLICATION_PATH_SET . DS . 'general.php') ) {
  $generalConfig = include APPLICATION_PATH_SET . DS . 'general.php';
} else {
  $generalConfig = array('environment_mode' => 'production');
}

// development mode
$application_env = @$generalConfig['environment_mode'];
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (
  !empty($_SERVER['_ENGINE_ENVIRONMENT']) ? $_SERVER['_ENGINE_ENVIRONMENT'] : (
  $application_env ? $application_env :
  'production'
)));

// Sub apps
if( !defined('_ENGINE_R_MAIN') && !defined('_ENGINE_R_INIT') ) {
  if( @$_GET['m'] == 'css' ) {
    define('_ENGINE_R_MAIN', 'css.php');
    define('_ENGINE_R_INIT', false);
  } else if( @$_GET['m'] == 'lite' ) {
    define('_ENGINE_R_MAIN', 'lite.php');
    define('_ENGINE_R_INIT', true);
  } else {
    define('_ENGINE_R_MAIN', false);
    define('_ENGINE_R_INIT', true);
  }
}

// Boot
if( _ENGINE_R_INIT ) {
  
  // Application
  require_once 'Engine/Loader.php';
  require_once 'Engine/Application.php';

  // Create application, bootstrap, and run
  $application = new Engine_Application(
    array(
      'environment' => APPLICATION_ENV,
      'bootstrap' => array(
        'path' => APPLICATION_PATH_COR . DS . 'modules' . DS . APPLICATION_NAME . DS . 'Bootstrap.php',
        'class' => ucfirst(APPLICATION_NAME) . '_Bootstrap',
      ),
      'autoloaderNamespaces' => array(
        'Zend'      => APPLICATION_PATH_LIB . DS . 'Zend',
        'Engine'    => APPLICATION_PATH_LIB . DS . 'Engine',
        //'Plugin'    => APPLICATION_PATH_PLU,
				'Core' => APPLICATION_PATH_COR . DS . 'modules' . DS . 'Core',
        'Sesapi' => APPLICATION_PATH_COR . DS . 'modules' . DS . 'Sesapi',
          'Sespage' => APPLICATION_PATH_COR . DS . 'modules' . DS . 'Sespage',
          'Sesgroup' => APPLICATION_PATH_COR . DS . 'modules' . DS . 'Sesgroup',
          'Sesbusiness' => APPLICATION_PATH_COR . DS . 'modules' . DS . 'Sesbusiness',
      ),
    )
  );
  Engine_Application::setInstance($application);
  Engine_Api::getInstance()->setApplication($application);

}
	$applicationName = ucfirst(APPLICATION_NAME);
	$nameClass = $applicationName . '_Bootstrap';
  $sesApiBootstrap =  new $nameClass($application);
	$sesApiBootstrap->bootstrap();
  $sesApiBootstrap->run($application);
