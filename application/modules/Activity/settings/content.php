<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */
return array(
  array(
    'title' => 'Activity Feed',
    'description' => 'Displays the activity feed.',
    'category' => 'Core',
    'type' => 'widget',
    'name' => 'activity.feed',
    'defaultParams' => array(
      'title' => 'What\'s New',
      'max_photo' => '9',
    ),
    'adminForm' => array(
      'elements' => array(
        array(
          'Select', 'max_photo', array(
            'label' => 'Maximum Photos displayed in Activity Feed',
            'description' => 'Enter the maximum number of photos that you want to display as an attachment in the'
            . ' activity feed when multiple photos are uploaded by a user. Photos exceeding this value can be viewed'
            . ' by clicking the "+" thumbnail at the end of a photo stream in the activity feed.',
            'value' => 8,
            'multiOptions' => array(
              0 => 0,
              1 => 1,
              2 => 2,
              3 => 3,
              4 => 4,
              5 => 5,
              6 => 6,
              7 => 7,
              8 => 8,
              9 => 9,
              10 => 10,
              11 => 11,
              12 => 12,
            )
          )
        ),
      )
    ),
  ),
  array(
    'title' => 'Requests',
    'description' => 'Displays the current logged-in member\'s requests (i.e. friend requests, group invites, etc).',
    'category' => 'Core',
    'type' => 'widget',
    'name' => 'activity.list-requests',
    'defaultParams' => array(
      'title' => 'Requests',
    ),
    'requirements' => array(
      'viewer',
    ),
  ),
) ?>
