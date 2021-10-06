<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: manifest.php 10267 2014-06-10 00:55:28Z lucas $
 * @author     John
 */
return array(
  // Package -------------------------------------------------------------------
  'package' => array(
    'type' => 'module',
    'name' => 'activity',
    'version' => '5.7.0',
    'revision' => '$Revision: 10267 $',
    'path' => 'application/modules/Activity',
    'repository' => 'socialengine.com',
    'title' => 'Activity',
    'description' => 'Activity',
    'author' => 'Webligo Developments',
    'dependencies' => array(
      array(
        'type' => 'module',
        'name' => 'core',
        'minVersion' => '5.0.0',
      ),
    ),
    'actions' => array(
       'install',
       'upgrade',
       'refresh',
       //'enable',
       //'disable',
     ),
    'callback' => array(
      'path' => 'application/modules/Activity/settings/install.php',
      'class' => 'Activity_Installer',
      'priority' => 4000,
    ),
    'directories' => array(
      'application/modules/Activity',
    ),
    'files' => array(
      'application/languages/en/activity.csv',
    ),
  ),
  // Hooks ---------------------------------------------------------------------
  'hooks' => array(
    array(
      'event' => 'onActivityActionCreateAfter',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onActivityActionDeleteBefore',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onActivityActionUpdateAfter',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'getActivity',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'addActivity',
      'resource' => 'Activity_Plugin_Core',
    ),
    array(
      'event' => 'onItemDeleteBefore',
      'resource' => 'Activity_Plugin_Core',
    ),
  ),
  // Items ---------------------------------------------------------------------
  'items' => array(
    'activity_action',
    'activity_comment',
    'activity_like',
    'activity_notification',
  ),
  // Routes --------------------------------------------------------------------
  'routes' => array(
    'recent_activity' => array(
      'route' => 'activity/notifications/:action/*',
      'defaults' => array(
        'module' => 'activity',
        'controller' => 'notifications',
        'action' => 'index',
      )
    )
  )
) ?>
