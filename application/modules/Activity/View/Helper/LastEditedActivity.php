<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Activity.php 9799 2012-10-16 22:11:00Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_View_Helper_LastEditedActivity extends Zend_View_Helper_Abstract
{

  public function lastEditedActivity($action)
  {
    $time = strtotime($action->modified_date);
    if( strtotime($action->modified_date) <= $action->getTimeValue() || !$action->getTypeInfo()->editable ) {
      return;
    }
    // Prepare data in locale timezone
    $timezone = null;
    if( Zend_Registry::isRegistered('timezone') ) {
      $timezone = Zend_Registry::get('timezone');
    }
    if( null !== $timezone ) {
      $prevTimezone = date_default_timezone_get();
      date_default_timezone_set($timezone);
    }

    $date = date("D, j M Y G:i:s O", $time);
    if( null !== $timezone ) {
      date_default_timezone_set($prevTimezone);
    }
    return $this->view->partial(
        '_lastEdited.tpl', 'activity', array(
        'action' => $action,
        'lastEditedDate' => $date
        )
    );
  }
}
