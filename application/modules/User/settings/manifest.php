<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
return array(
    // Package -------------------------------------------------------------------
    'package' => array(
        'type' => 'module',
        'name' => 'user',
        'version' => '5.7.0',
        'revision' => '$Revision: 10271 $',
        'path' => 'application/modules/User',
        'repository' => 'socialengine.com',
        'title' => 'Members',
        'description' => 'Members',
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
            'path' => 'application/modules/User/settings/install.php',
            'class' => 'User_Installer',
            'priority' => 3000,
        ),
        'directories' => array(
            'application/modules/User',
        ),
        'files' => array(
            'application/languages/en/user.csv',
        ),
    ),
    // Compose -------------------------------------------------------------------
    'compose' => array(
        array('_composeFacebook.tpl', 'user'),
        array('_composeTwitter.tpl', 'user'),
    ),
    'composer' => array(
        'facebook' => array(
            'script' => array('_composeFacebook.tpl', 'user'),
        ),
        'twitter' => array(
            'script' => array('_composeTwitter.tpl', 'user'),
        ),
    ),
    // Hooks ---------------------------------------------------------------------
    'hooks' => array(
        array(
            'event' => 'onUserEnable',
            'resource' => 'User_Plugin_Core',
        ),
        array(
            'event' => 'onUserDeleteBefore',
            'resource' => 'User_Plugin_Core',
        ),
        array(
            'event' => 'onUserCreateAfter',
            'resource' => 'User_Plugin_Core',
        ),
        array(
            'event' => 'getAdminNotifications',
            'resource' => 'User_Plugin_Core',
        ),
        array(
            'event' => 'onActivityActionUpdateAfter',
            'resource' => 'User_Plugin_Core',
        )
    ),
    // Items ---------------------------------------------------------------------
    'items' => array(
        'user',
        'user_list',
        'user_list_item',
        'user_code',
    ),
    // Routes --------------------------------------------------------------------
    'routes' => array(
        // User - General
        'user_extended' => array(
            'route' => 'members/:controller/:action/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'index'
            ),
            'reqs' => array(
                'controller' => '\D+',
                'action' => '\D+',
            )
        ),
        'user_general' => array(
            'route' => 'members/:action/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'index',
                'action' => 'browse'
            ),
            'reqs' => array(
                'action' => '(home|browse)',
            )
        ),
        'user_coverphoto' => array(
            'route' => 'user/coverphoto/:action/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'coverphoto',
            ),
            'reqs' => array(
                'action' => '(get-cover-photo|get-main-photo|reset-cover-photo-position'
                    . '|upload-cover-photo|choose-from-albums|remove-cover-photo)'
            ),
        ),

        // User - Specific
        'user_profile' => array(
            'route' => 'profile/:id/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'profile',
                'action' => 'index'
            )
        ),

        'user_login' => array(
            //'type' => 'Zend_Controller_Router_Route_Static',
            'route' => '/login/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'auth',
                'action' => 'login'
            )
        ),
        'user_logout' => array(
            'type' => 'Zend_Controller_Router_Route_Static',
            'route' => '/logout',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'auth',
                'action' => 'logout'
            )
        ),
        'user_signup' => array(
            'route' => '/signup/:action/*',
            'defaults' => array(
                'module' => 'user',
                'controller' => 'signup',
                'action' => 'index'
            )
        ),
    )
); ?>
