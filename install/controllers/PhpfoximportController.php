<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: ImportController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class PhpfoximportController extends Zend_Controller_Action
{

  /**
   * @var Zend_Cache_Core
   */
  protected $_cache;

  /**
   * @var string
   */
  protected $_platform;

  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_fromDb;

  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_fromPrefix;

  /**
   * @var string
   */
  protected $_fromPath;

  /**
   * @var Zend_Db_Adapter_Abstract
   */
  protected $_toDb;

  /**
   * @var string
   */
  protected $_toPath;
  protected $_email;
  protected $_emailOptions;
  protected $_emailTimeout = 10;
  protected $_ignoreShutdown = false;

  public function init()
  {
    // Get cache
    if( !Zend_Registry::isRegistered('Cache') ) {
      throw new Engine_Exception('Caching is required, please ensure temporary/cache is writable.');
    }
    $oldCache = Zend_Registry::get('Cache');

    // Make new cache
    $this->_cache = Zend_Cache::factory('Core', $oldCache->getBackend(), array(
        'cache_id_prefix' => 'engine4installimport',
        'lifetime' => 7 * 86400, // For those extra large imports
        'ignore_user_abort' => true,
        'automatic_serialization' => true,
    ));

    // Get existing token
    $token = $this->_cache->load('token', true);

    // Check if already logged in
    if( !Zend_Registry::get('Zend_Auth')->getIdentity() ) {
      // Check if token matches
      if( null == $this->_getParam('token') ) {
        return $this->_helper->redirector->gotoRoute(array(), 'default', true);
      } elseif( $token !== $this->_getParam('token') ) {
        echo Zend_Json::encode(array(
          'status' => false,
          'erros' => 'Invalid token',
        ));
        exit();
      }
    }

    // Add path to autoload
    Zend_Registry::get('Autoloader')->addResourceType('import', 'import', 'Import');


    $this->view->layout()->mainNavigation->findBy('controller', 'import')->setActive();
  }

  public function indexAction()
  {
    $this->_helper->redirector->gotoRoute(array('action' => 'phpfox-instructions'));
  }

  // Phpfox

  public function phpfoxInstructionsAction()
  {
    $this->view->dbHasContent = $this->_dbHasContent();
  }

  public function phpfoxAction()
  {
    // Set platform
    $this->_platform = 'phpfox';
    $this->view->warningArr = array();

    // Hack to allow resuming
    if( $this->_getParam('bypass') ) {
      $this->view->token = $token = md5(time() . get_class($this) . rand(1000000, 9999999));
      $this->_cache->save($token, 'token');
      $this->_ignoreShutdown = true;
      return $this->_helper->viewRenderer->setScriptAction('phpfox-split');
    }

    // Make form
    $this->view->form = $form = new Install_Form_Import_Phpfox();

    // Populate steps
    $steps = $this->_listMigrators($this->_platform);
    $form->disabledSteps->setMultiOptions(array_combine($steps, $steps));

    // Populate admin email
    if( Zend_Registry::isRegistered('Zend_Db') && ($db = Zend_Registry::get('Zend_Db')) instanceof Zend_Db_Adapter_Abstract ) {
      try {
        $email = $db->query('
          SELECT email
          FROM engine4_users
          RIGHT JOIN engine4_authorization_levels
          ON engine4_authorization_levels.level_id=engine4_users.level_id
          WHERE flag = \'superadmin\'
          ORDER BY user_id ASC
          LIMIT 1
        ')->fetchColumn(0);
        $form->populate(array(
          'email' => $email,
          'emailid_superadmin' => $email
        ));
      } catch( Exception $e ) {
        // Silence
      }
    }

    // Check post
    // Check method/data validitiy
    if( !$this->getRequest()->isPost() ) {
      return;
    }
    if( !$form->isValid($this->getRequest()->getPost()) ) {
      $values = $form->getValues();
      if( !empty($values['bannedUserGroupId']) && !ctype_digit($values['bannedUserGroupId']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Banned user group id');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( !empty($values['adminUserGroupId']) && !ctype_digit($values['adminUserGroupId']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Administrator user group id');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( !empty($values['registeredUserGroupId']) && !ctype_digit($values['registeredUserGroupId']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Registered user group id');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( !empty($values['guestUserGroupId']) && !ctype_digit($values['guestUserGroupId']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Guest user group id');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( !empty($values['staffUserGroupId']) && !ctype_digit($values['staffUserGroupId']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Staff user group id');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( empty($values['import_group_sepage']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please select the option for importing PHPfox Pages content');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( empty($values['import_group_segroup']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please select the option for importing PHPfox Groups content');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( empty($values['import_event_seevent']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please select the option for importing PHPfox Events content');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( empty($values['import_blog_seblog']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please select the option for importing PHPfox Blogs content');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }
      if( empty($values['import_classified_seclassified']) ) {
        $error = Zend_Registry::get('Zend_Translate')->_('Please select the option for importing PHPfox Marketplace Listing (Classifieds) content');
        $form->getDecorator('errors')->setOption('escape', false);
        $form->addError($error);
      }

      return;
    }
    $values = $form->getValues();
    $isError = false;
    if( !empty($values['bannedUserGroupId']) && !ctype_digit($values['bannedUserGroupId']) ) {
      $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Banned user group id');
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);
      $isError = true;
    }
    if( !empty($values['adminUserGroupId']) && !ctype_digit($values['adminUserGroupId']) ) {
      $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Administrator user group id');
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);
      $isError = true;
    }
    if( !empty($values['registeredUserGroupId']) && !ctype_digit($values['registeredUserGroupId']) ) {
      $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Registered user group id');
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);
      $isError = true;
    }
    if( !empty($values['guestUserGroupId']) && !ctype_digit($values['guestUserGroupId']) ) {
      $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Guest user group id');
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);
      $isError = true;
    }
    if( !empty($values['staffUserGroupId']) && !ctype_digit($values['staffUserGroupId']) ) {
      $error = Zend_Registry::get('Zend_Translate')->_('Please enter the valid Staff user group id');
      $form->getDecorator('errors')->setOption('escape', false);
      $form->addError($error);
      $isError = true;
    }
    if( $isError )
      return;
    // Clean cache
    if( empty($values['skipClearCache']) || $values['skipClearCache'] == '0' ) {
      $this->_cache->clean();
    }

    // get params
    $params = $values;
    unset($params['path']);
    unset($params['mode']);

    if( $values['phpfox_version'] == 1 ) {
      $fromDbConfigFile = $values['path'] . '/include/setting/server.sett.php';
    } else {
      $values['path'] .= '/PF.Base';
      $fromDbConfigFile = $values['path'] . '/file/settings/server.sett.php';
    }
    // Setup
    try {
      $this->_setupPhpfox($values['path'], APPLICATION_PATH, $fromDbConfigFile);
    } catch( Exception $e ) {
      return $form->addError($e->getMessage());
    }

    // Store config locations in cache 
    $this->_cache->save($fromDbConfigFile, 'fromDbConfig');
    $this->_cache->save($values['path'], 'fromPath');
    $this->_cache->save(APPLICATION_PATH, 'toPath');
    $this->_cache->save($params, 'params');
    $this->_cache->save(microtime(true), 'startTime');
    $this->_cache->save((array) $values['disabledSteps'], 'disabledSteps');


    // Setup email
    $this->_email = @$params['email'];
    $this->_emailOptions = (array) @$params['emailOptions'];
    $this->_emailTimeout = (array) @$params['emailTimeout'];

    register_shutdown_function(array($this, 'phpfoxShutdown'));


    // Send start email
    if( $this->_email && in_array('start', $this->_emailOptions) ) {
      try {
        $now = gmdate('c', time());
        $mail = new Zend_Mail();
        $mail
          ->setFrom('no-reply@' . $_SERVER['HTTP_HOST'])
          ->addTo($this->_email)
          ->setSubject('SocialEngine PHP Migration Progress for ' . $_SERVER['HTTP_HOST'])
          ->setBodyText("Hello,

This is a SocialEngine PHP migration progress report.

Server: {$_SERVER['HTTP_HOST']}
Time: {$now}

Message: Migration is starting.

Regards,
Your Server")
        ;
        $mail->send();
      } catch( Exception $e ) {
        // Silence and disable emails?
        $this->_email = null;
        unset($params['email']);
        $this->_cache->save($params, 'params');
      }
    }



    // Do import
    // Split mode
    if( $values['mode'] == 'split' ) {
      $this->view->token = $token = md5(time() . get_class($this) . rand(1000000, 9999999));
      $this->_cache->save($token, 'token');
      $this->_ignoreShutdown = true;
      return $this->_helper->viewRenderer->setScriptAction('phpfox-split');
    } else {
      // Set time limit
      $importStartTime = time();
      @set_time_limit(0);

      @ini_set('max_execution_time', 600);
      //$params = array();
      $messages = array();
      $hasError = false;
      $hasWarning = false;
      foreach( $this->_getAllMigrators($this->_platform, $params, $values['disabledSteps']) as $migrator ) {
        if( in_array(array_pop(explode('_', get_class($migrator))), (array) @$values['disabledSteps']) )
          continue;

        $migrator->run();
        $messages = array_merge($messages, $migrator->getMessages());
        $hasError |= $migrator->hasError();
        $hasWarning |= $migrator->hasWarning();
        unset($migrator);
      }

      $importEndTime = time();
      $importDeltaTime = $importEndTime - $importStartTime;

      $this->view->status = 1;
      $this->view->messages = $messages;
      $this->view->hasError = $hasError;
      $this->view->hasWarning = $hasWarning;
      $this->view->form = null;
      $this->view->importDeltaTime = $importDeltaTime;
    }

    $this->_ignoreShutdown = true;
  }

  public function phpfoxRemoteAction()
  {
    // Set platform
    $this->_platform = 'phpfox';

    $starttime = microtime(true);
    @set_time_limit(0);
    @ini_set('max_execution_time', 600);
    @ini_set('memory_limit', '-1');
    ignore_user_abort(true); // Not sure if we should do this
    register_shutdown_function(array($this, 'phpfoxShutdown'));
    // Check token
    $token = $this->_cache->load('token', true);
    if( $token !== $this->_getParam('token') ) {
      $errorOutput = '';
      while( ob_get_level() > 0 ) {
        $errorOutput .= ob_get_clean();
      }
      echo Zend_Json::encode(array(
        'status' => false,
        'error' => 'Bad token',
        'errorOutput' => $errorOutput,
      ));
      // We want an error
      //$this->_ignoreShutdown = true;
      error_reporting(0);
      trigger_error('Invalid token', E_USER_ERROR);
      exit();
    }
    // Check cache
    if( !$this->_cache ) {
      $errorOutput = '';
      while( ob_get_level() > 0 ) {
        $errorOutput .= ob_get_clean();
      }
      echo Zend_Json::encode(array(
        'status' => false,
        'error' => 'No cache',
        'errorOutput' => $errorOutput,
      ));
      // We want an error
      //$this->_ignoreShutdown = true;
      error_reporting(0);
      trigger_error('Cache invalid', E_USER_ERROR);
      exit();
    }
    // Setup
    try {
      $this->_setupPhpfox($this->_cache->load('fromPath', true), $this->_cache->load('toPath', true), $this->_cache->load('fromDbConfig', true));
    } catch( Exception $e ) {
      $errorOutput = '';
      while( ob_get_level() > 0 ) {
        $errorOutput .= ob_get_clean();
      }
      echo Zend_Json::encode(array(
        'status' => false,
        'error' => $e->getMessage(),
      ));
      // We want an error
      //$this->_ignoreShutdown = true;
      error_reporting(0);
      trigger_error($e->getMessage(), E_USER_ERROR);
      exit();
    }



    // Params
    if( false == ($params = $this->_cache->load('params', true)) || !is_array($params) ) {
      $params = array();
    }

    $disabledSteps = (array) $this->_cache->load('disabledSteps', true);

    $this->_email = @$params['email'];
    $this->_emailOptions = (array) @$params['emailOptions'];
    $this->_emailTimeout = (integer) @$params['emailTimeout'];
    if( !$this->_emailTimeout ) {
      $this->_emailTimeout = 10;
    }


    // Check for index or build if not found
    if( false == ($index = $this->_cache->load('objectindex', true)) ) {
      $totalRecords = 0;
      $index = array();
      $migrators = $this->_getAllMigrators($this->_platform, $params, $disabledSteps);
      foreach( $migrators as $migrator ) {
        $class = get_class($migrator);
        $key = 'object' . $class;
        $index[] = $key;
        $totalRecords += $migrator->getTotalFromRecords();
        $this->_cache->save($migrator, $key);
      }
      $this->_cache->save($index, 'objectindex');
      $this->_cache->save($totalRecords, 'totalrecords');
    }
    $totalRecords = (int) $this->_cache->load('totalrecords', true);
    $totalProcessed = (int) $this->_cache->load('totalprocessed', true);
    $total = count($index);




    // Check for the last migrator executed
    if( false == ($last = $this->_cache->load('objectlast', true)) ) {
      $last = 0;
    }



    // Calculate time running
    $startTime = $this->_cache->load('startTime', true);
    $endTime = microtime(true);
    $deltaTime = $endTime - $startTime;
    $deltaTimeStr = $this->_deltaTime($deltaTime);

    // Calculate time remaining
    if( $deltaTime == 0 || $totalRecords == 0 || $totalProcessed == 0 ) {
      $ratioComplete = 0;
      $percentComplete = 0;
      $timeRemaining = 0;
      $timeRemainingStr = null;
    } else {
      $ratioComplete = $totalProcessed / $totalRecords;
      $percentComplete = round($ratioComplete * 100, 1);
      $estimatedTotalTime = $deltaTime / $ratioComplete;
      $timeRemaining = $estimatedTotalTime - $deltaTime;
      $timeRemainingStr = $this->_deltaTime($timeRemaining);
    }




    // Check if we're done
    if( $last >= $total ) {

      $hasError = $this->_cache->load('haserror', true);
      $hasWarning = $this->_cache->load('haswarning', true);

      // Send completion email
      if( $this->_email && in_array('complete', $this->_emailOptions) ) {
        try {
          $now = gmdate('c', time());
          $hasErrorString = ( $hasError ? 'There were errors encountered during the import.' : '' );
          $hasWarningString = ( $hasWarning ? 'There were warnings encountered during the import.' : '' );
          $mail = new Zend_Mail();
          $mail
            ->setFrom('no-reply@' . $_SERVER['HTTP_HOST'])
            ->addTo($this->_email)
            ->setSubject('SocialEngine PHP Migration Progress for ' . $_SERVER['HTTP_HOST'])
            ->setBodyText("Hello,


This is a SocialEngine PHP migration progress report.


Message : Migration is complete!
{$hasErrorString}
{$hasWarningString}




Overall :

Server: {$_SERVER['HTTP_HOST']}
Time: {$now}
Duration: {$deltaTimeStr} have passed.
Processed: {$totalRecords} records were processed.


Regards,
Your Server")
          ;
          $mail->send();
        } catch( Exception $e ) {
          // Silence
        }
      }

      // Clear token on complete
      $this->_cache->save('', 'token');

      // JSON response
      $errorOutput = '';
      while( ob_get_level() > 0 ) {
        $errorOutput .= ob_get_clean();
      }
      echo Zend_Json::encode(array(
        'status' => true,
        'complete' => true,
        'error' => 'Complete',
        'hasError' => $hasError,
        'hasWarning' => $hasWarning,
        'deltaTime' => $deltaTime,
        'deltaTimeStr' => $deltaTimeStr,
        'ratioComplete' => $ratioComplete,
        'timeRemaining' => $timeRemaining,
        'timeRemainingStr' => $timeRemainingStr,
        'migratorCurrent' => $total,
        'migratorTotal' => $total,
        'totalRecords' => $totalRecords,
        'totalProcessed' => $totalProcessed,
        'errorOutput' => $errorOutput,
      ));
      $this->_ignoreShutdown = true;
      exit();
    }




    // Get the migrator
    $key = $index[$last];
    $migrator = $this->_cache->load($key, true);
    if( !$migrator instanceof Install_Import_PhpfoxAbstract ) {
      $errorOutput = '';
      while( ob_get_level() > 0 ) {
        $errorOutput .= ob_get_clean();
      }
      echo Zend_Json::encode(array(
        'status' => false,
        'error' => 'Missing migrator',
      ));
      // We want an error
      //$this->_ignoreShutdown = true;
      error_reporting(0);
      trigger_error('Missing migrator', E_USER_ERROR);
      exit();
    }

    // Repopulate config
    $migrator->setOptions(array_merge($params, array(
      'fromDb' => $this->_fromDb,
      'toDb' => $this->_toDb,
      'fromPath' => $this->_fromPath,
      'toPath' => $this->_toPath,
      'cache' => $this->_cache,
      'fromPrefix' => $this->_fromPrefix
    )));

    // Run migrator (if not disabled)
    $migratorTotal = 0;
    $migratorProcessed = 0;
    $currentProcessed = 0;
    $migratorPercent = 0;
    if( !in_array(array_pop(explode('_', get_class($migrator))), $disabledSteps) ) {
      $migratorTotal = $migrator->getTotalFromRecords();
      $previousRunCount = $migrator->getRunCount();
      $migrator->run();
      $migratorProcessed = $migrator->getRunCount();
      $currentProcessed = $migratorProcessed - $previousRunCount;
      $totalProcessed += $currentProcessed;
      if( $migratorTotal > 0 ) {
        $migratorPercent = round(100 * $migratorProcessed / $migratorTotal, 1);
      }
      $this->_cache->save($totalProcessed, 'totalprocessed');
    }




    // Check if migrator is done
    if( $migrator->isComplete() ) {

      // Send progress email
      if( $this->_email && in_array('step', $this->_emailOptions) ) {
        try {
          $currentStep = get_class($migrator);
          $now = gmdate('c', time());
          $hasErrorString = ( $migrator->hasError() ? 'There were errors encountered during the import.' : '' );
          $hasWarningString = ( $migrator->hasWarning() ? 'There were warnings encountered during the import.' : '' );
          $messages = join("\n", $migrator->getMessages());
          $mail = new Zend_Mail();
          $mail
            ->setFrom('no-reply@' . $_SERVER['HTTP_HOST'])
            ->addTo($this->_email)
            ->setSubject('SocialEngine PHP Migration Progress for ' . $_SERVER['HTTP_HOST'])
            ->setBodyText("Hello,


This is a SocialEngine PHP migration progress report.


Message : Step {$last} ({$currentStep}) of {$total} has been completed.
          {$migratorProcessed} records were processed in this step.

Details:
{$hasErrorString}
{$hasWarningString}
{$messages}




Overall : 

Server: {$_SERVER['HTTP_HOST']}
Time: {$now}
Duration: {$deltaTimeStr} have passed, {$timeRemainingStr} remaining.
Processed: {$totalProcessed} of {$totalRecords} records have been processed.
Progress: {$percentComplete} complete.


Regards,
Your Server")
          ;
          $mail->send();
        } catch( Exception $e ) {
          // Silence
          $migrator->getLog()->log('Failed to send progress email: ' . $e->__toString(), Zend_Log::WARN);
        }
      }



      // Use next in next request
      $last++;
      $this->_cache->save($last, 'objectlast');
    }



    // Put the migrator back
    $this->_cache->save($migrator, $key);



    // Store error/warning flags for later
    if( $migrator->hasError() ) {
      $this->_cache->save(true, 'haserror');
    }
    if( $migrator->hasWarning() ) {
      $this->_cache->save(true, 'haswarning');
    }



    // Send progress email
    if( $this->_email && in_array('timeout', $this->_emailOptions) && $this->_emailTimeout ) {
      $lastEmail = $this->_cache->load('lastEmail', true);
      /* if( !$lastEmail ) {
        $this->_cache->save(time(), 'lastEmail');
        $migrator->getLog()->log('Test: ' . $lastEmail, Zend_Log::WARN);
        } else if( $lastEmail && time() > ($this->_emailTimeout * 60) + $lastEmail ) {
       * 
       */
      if( !$lastEmail || time() > ($this->_emailTimeout * 60) + $lastEmail ) {
        $this->_cache->save(time(), 'lastEmail');
        try {
          $currentStep = get_class($migrator);
          $lastReport = gmdate('c', $lastEmail);
          $now = gmdate('c', time());
          $hasErrorString = ( $migrator->hasError() ? 'There were errors encountered during the import.' : '' );
          $hasWarningString = ( $migrator->hasWarning() ? 'There were warnings encountered during the import.' : '' );
          $messages = join("\n", $migrator->getMessages());
          $mail = new Zend_Mail();
          $mail
            ->setFrom('no-reply@' . $_SERVER['HTTP_HOST'])
            ->addTo($this->_email)
            ->setSubject('SocialEngine PHP Migration Progress for ' . $_SERVER['HTTP_HOST'])
            ->setBodyText("Hello,


This is a SocialEngine PHP migration progress report.


Message : Currently on {$last} ({$currentStep}) of {$total} steps.
          Progress report being sent every {$this->_emailTimeout} minutes. Last report was at {$lastReport}.
          {$migratorProcessed} of {$migratorTotal} records for this step have been processed.
          This step is {$migratorPercent}% complete.

Details:
{$hasErrorString}
{$hasWarningString}
{$messages}




Overall : 

Server: {$_SERVER['HTTP_HOST']}
Time: {$now}
Duration: {$deltaTimeStr} have passed, {$timeRemainingStr} remaining.
Processed: {$totalProcessed} of {$totalRecords} records have been processed.
Progress: {$percentComplete} complete.


Regards,
Your Server")
          ;
          $mail->send();
        } catch( Exception $e ) {
          // Silence
          $migrator->getLog()->log('Failed to send progress email: ' . $e->__toString(), Zend_Log::WARN);
        }
      }
    }




    // Send back progress report
    $errorOutput = '';
    while( ob_get_level() > 0 ) {
      $errorOutput .= ob_get_clean();
    }
    echo Zend_Json::encode(array(
      'status' => true,
      'className' => get_class($migrator),
      'hasError' => $migrator->hasError(),
      'hasWarning' => $migrator->hasWarning(),
      'messages' => $migrator->getMessages(),
      'deltaTime' => $deltaTime,
      'deltaTimeStr' => $deltaTimeStr,
      'ratioComplete' => $ratioComplete,
      'timeRemaining' => $timeRemaining,
      'timeRemainingStr' => $timeRemainingStr,
      'migratorCurrent' => $last + 1,
      'migratorTotal' => $total,
      'totalRecords' => $totalRecords,
      'totalProcessed' => $totalProcessed,
      'currentProcessed' => $currentProcessed,
      'migratorProcessed' => $migratorProcessed,
      'migratorRecords' => $migratorTotal,
      'migratorPercent' => $migratorPercent,
      'errorOutput' => $errorOutput,
    ));



    $this->_ignoreShutdown = true;
    exit();
  }

  protected function _listMigrators($platform)
  {
    $types = array();
    $path = APPLICATION_PATH . '/install/import/' . ucfirst($platform);
    foreach( scandir($path) as $child ) {
      if( strlen($child) <= 4 )
        continue;
      if( substr($child, -4) !== '.php' )
        continue;
      if( stripos($child, 'Abstract') !== false )
        continue;
      $types[] = substr($child, 0, -4);
    }
    return $types;
  }

  protected function _getAllMigrators($platform, array $params = array(), array $disabledSteps = array())
  {
    $migrators = array();
    $priorities = array();
    //$requires = array();
    foreach( $this->_listMigrators($platform) as $type ) {
      if( in_array($type, $disabledSteps) )
        continue;
      $migrator = $this->_getMigrator($platform, $type, $params);
      $priorities[$type] = $migrator->getPriority();
      $migrators[$type] = $migrator;
    }
    arsort($priorities);

    $sortedMigrators = array();
    foreach( $priorities as $type => $priority ) {
      $sortedMigrators[] = $migrators[$type];
    }

    return $sortedMigrators;
  }

  /**
   * @param string $platform
   * @param string $type
   * @return Install_Import_Abstract
   */
  protected function _getMigrator($platform, $type, array $params = array())
  {
    $class = 'Install_Import_' .
      ucfirst($platform) . '_' .
      str_replace(' ', '', ucwords(trim(preg_replace('/[^a-zA-Z0-9_]+/', ' ', $type))));

    if( !class_exists($class, false) ) {
      $autoloader = Zend_Registry::get('Autoloader');
      $autoloader->autoload($class);
      if( !class_exists($class, false) ) {
        throw new Engine_Exception('Class not found: ' . $class);
      }
    }

    return new $class(array_merge($params, array(
        'fromDb' => $this->_fromDb,
        'toDb' => $this->_toDb,
        'fromPath' => $this->_fromPath,
        'toPath' => $this->_toPath,
        'cache' => $this->_cache,
        'fromPrefix' => $this->_fromPrefix
    )));
  }

  protected function _importValues($file)
  {

    include $file;
    return array_diff_key(get_defined_vars(), $GLOBALS, array('file' => null));
  }

  protected function _dbHasContent()
  {
    // Check for db
    $db = Zend_Registry::get('Zend_Db');
    if( !($db instanceof Zend_Db_Adapter_Abstract) ) {
      throw new Engine_Exception('SocialEngine has not yet been installed.');
    }

    $limits = array(
      'engine4_users' => 1,
      'engine4_activity_actions' => 10,
      'engine4_album_albums' => 0,
      'engine4_blog_blogs' => 0,
      'engine4_classified_classifieds' => 0,
      'engine4_event_events' => 0,
      //'engine4_forum_forums'
      'engine4_group_groups' => 0,
      'engine4_music_playlist_songs' => 0,
      'engine4_poll_polls' => 0,
      'engine4_video_videos' => 0,
    );

    foreach( $limits as $table => $limit ) {
      try {
        // Check if table exists
        $col = $db->query('SHOW TABLES LIKE ' . $db->quote($table))->fetchColumn(0);
        if( !$col ) {
          continue;
        }

        // Get count
        $count = $db->select()
          ->from($table, new Zend_Db_Expr('COUNT(*)'))
          ->query()
          ->fetchColumn(0)
        ;

        if( $count > $limit ) {
          return true;
        }
      } catch( Exception $e ) {
        // Silence
      }
    }

    return false;
  }

  protected function _deltaTime($deltaTime)
  {
    $hours = floor($deltaTime / 3600);
    $minutes = floor(($deltaTime % 3600) / 60);
    $seconds = floor((($deltaTime % 3600) % 60));

    $deltaTimeStr = '';
    if( $hours > 0 ) {
      $deltaTimeStr .= $this->view->translate(array('%d hour', '%d hours', $hours), $hours);
      $deltaTimeStr .= ', ';
    }
    if( $minutes > 0 ) {
      $deltaTimeStr .= $this->view->translate(array('%d minute', '%d minutes', $minutes), $minutes);
      $deltaTimeStr .= ', ';
    }
    $deltaTimeStr .= $this->view->translate(array('%d second', '%d seconds', $seconds), $seconds);
    if( $minutes > 0 || $hours > 0 ) {
      $deltaTimeStr .= ' (';
      $deltaTimeStr .= $this->view->translate(array('%s second total', '%s seconds total', $deltaTime), number_format($deltaTime));
      $deltaTimeStr .= ')';
    }

    return $deltaTimeStr;
  }

  // More utility

  protected function _setupPhpfox($fromPath, $toPath, $fromDbConfigFile)
  {
    // Check from path exists
    if( !is_dir($fromPath) ) {
      throw new Engine_Exception('Specified path is not a phpfox installation');
    }

    // Check to path exists
    if( !is_dir($toPath) ) {
      throw new Engine_Exception('Specified path is not a version 4 installation');
    }

    // Check from database config file
    define('PHPFOX', true);
    if( !is_file($fromDbConfigFile) ) {
      throw new Engine_Exception('PHPfox database config was not found.');
    }

    // Check to database config file
    $toDbConfigFile = $toPath . '/application/settings/database.php';
    if( !is_file($toDbConfigFile) ) {
      throw new Engine_Exception('Version 4 database config was not found.');
    }

    // Get from database config
    $fromDbConfig = $this->_importValues($fromDbConfigFile);

    if( !$fromDbConfig ||
      !is_array($fromDbConfig) ||
      empty($fromDbConfig['_CONF']['db']['host']) ||
      empty($fromDbConfig['_CONF']['db']['user']) ||
      empty($fromDbConfig['_CONF']['db']['pass']) ||
      empty($fromDbConfig['_CONF']['db']['name']) ) {
      throw new Engine_Exception('Invalid PHPfox database configuration.');
    }

    // Get to database config
    $toDbConfig = include $toDbConfigFile;
    if( !is_array($toDbConfig) ||
      empty($toDbConfig['adapter']) ||
      empty($toDbConfig['params']) ) {
      throw new Engine_Exception('Invalid version 4 database configuration.');
    }

    // Make from adapter and check connection
    $fromDbConfig = array(
      'adapter' => $toDbConfig['adapter'],
      'params' => array(
        'host' => $fromDbConfig['_CONF']['db']['host'],
        'username' => $fromDbConfig['_CONF']['db']['user'],
        'password' => $fromDbConfig['_CONF']['db']['pass'],
        'dbname' => $fromDbConfig['_CONF']['db']['name'],
        'charset' => $toDbConfig['params']['charset'],
        'adapterNamespace' => $toDbConfig['params']['adapterNamespace'],
        'fromPrefix' => $fromDbConfig['_CONF']['db']['prefix'],
      ),
    );
    try {
      $fromDb = Zend_db::factory($fromDbConfig['adapter'], $fromDbConfig['params']);
      $fromDb->getServerVersion(); // Forces connection
    } catch( Exception $e ) {
      throw new Engine_Exception('Database connection error: ' . $e->getMessage());
    }

    // Make to adapter and check connection
    try {
      $toDb = Zend_db::factory($toDbConfig['adapter'], $toDbConfig['params']);
      $toDb->getServerVersion(); // Forces connection
    } catch( Exception $e ) {
      throw new Engine_Exception('Database connection error: ' . $e->getMessage());
    }

    // Save all info for later
    $this->_fromDb = $fromDb;
    $this->_fromPath = $fromPath;
    $this->_toDb = $toDb;
    $this->_toPath = $toPath;
    $this->_fromPrefix = $fromDbConfig['params']['fromPrefix'];
  }

  public function phpfoxShutdown()
  {
    if( $this->_ignoreShutdown ) {
      return;
    }

    // Try to get message
    $message = '';
    if( function_exists('error_get_last') ) {
      $message = error_get_last();
      $message = 'The error was: '
        . $message['type'] . ' '
        . $message['message'] . ' '
        . $message['file'] . ' '
        . $message['line'];
    } else {
      $message = 'Unknown error message';
    }

    // Send death email
    if( $this->_email && in_array('start', $this->_emailOptions) ) {

      try {
        $now = gmdate('c', time());
        $mail = new Zend_Mail();
        $mail
          ->setFrom('no-reply@' . $_SERVER['HTTP_HOST'])
          ->addTo($this->_email)
          ->setSubject('SocialEngine PHP Migration Progress for ' . $_SERVER['HTTP_HOST'])
          ->setBodyText("Hello,


This is a SocialEngine PHP migration progress report.


Message : A fatal error has occurred. {$message}





Overall : 

Server: {$_SERVER['HTTP_HOST']}
Time: {$now}


Regards,
Your Server")
        ;
        $mail->send();
      } catch( Exception $e ) {
        // Silence
      }
    }

    // Send json?
    $errorOutput = '';
    while( ob_get_level() > 0 ) {
      $errorOutput .= ob_get_clean();
    }
    Zend_Json::encode(array(
      'status' => false,
      'error' => 'A fatal error has occurred: ' . $message,
      'errorOutput' => $errorOutput,
    ));
  }

  protected function isPluginExist($name)
  {
    $db = Zend_Registry::get('Zend_Db');
    $pluginEnabled = $db->select()
      ->from('engine4_core_modules', 'enabled')
      ->where('name = ?', $name)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( $pluginEnabled === FALSE )
      return false;
    return true;
  }

}
