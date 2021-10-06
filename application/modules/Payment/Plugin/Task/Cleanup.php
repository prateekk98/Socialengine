<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Cleanup.php 10098 2013-10-19 00:01:38Z jung $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Plugin_Task_Cleanup extends Core_Plugin_Task_Abstract
{
  public function execute()
  {
    $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');


    // Get subscriptions that have expired or have finished their trial period
    // (trial is not yet implemented)
    $select = $subscriptionsTable->select()
      ->where('expiration_date <= ?', new Zend_Db_Expr('NOW()'))
      ->where('status = ?', 'active')
      //->where('status IN(?)', array('active', 'trial'))
      ->order('subscription_id ASC')
      ->limit(50);

    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
        $package = $subscription->getPackage();
        // Check if the package has an expiration date
        $expiration = $package->getExpirationDate();
        if( !$expiration || !$package->hasDuration() || (time() > $package->getAdditionalExpirationDate(strtotime($subscription->expiration_date)))) {
            continue;
        }
        // It's expired
        // @todo send an email
        $subscription->onExpiration();
        if ($subscription->didStatusChange()) {
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($subscription->getUser(), 'payment_subscription_expired', array(
                'subscription_title' => $package->title,
                'queue'=>false,
                'subscription_description' => $package->description,
                'subscription_terms' => $package->getPackageDescription(),
                'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
                Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
        }
    }

    $select = $subscriptionsTable->select()
      ->where('expiration_date >= ?', new Zend_Db_Expr('NOW()'))
      ->where('status = ?', 'active')
      ->order('subscription_id ASC')
      ->limit(50);

    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $package = $subscription->getPackage();
      //Check send email and notfication reminder is enabled
      if(!empty($package->send_reminder)) {
        // Check if the package has an expiration date
        //remainder Mail
        $remainderEmail = $package->getReminderDate(strtotime($subscription->expiration_date));
        if(($remainderEmail < time()) && ($remainderEmail < strtotime($subscription->expiration_date))){
          $viewer = Engine_Api::_()->user()->getViewer();
          $alreadyExist = Engine_Api::_()->getDbtable('notifications', 'activity')->delete(array('type =?' => 'payment_subscription_expiredsoon', "subject_id =?" => $viewer->getIdentity(), "object_type =? " => $subscription->getUser()->getType(), "object_id = ?" => $subscription->getUser()->getIdentity()));
          Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($subscription->getUser(), $viewer, $subscription->getUser(), 'payment_subscription_expiredsoon',array('planName'=>$package->title,'period'=>date('Y-m-d H:i:s',strtotime($subscription->expiration_date))));
          //if($alreadyExist){
            Engine_Api::_()->getApi('mail', 'core')->sendSystem($subscription->getUser(), 'payment_subscription_expiredsoon', array(
              'plan_name' => $package->title,
              'queue'=>false,
              'period' => date('Y-m-d H:i:s',strtotime($subscription->expiration_date)),
              'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
              Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
            ));
          //}
        }
      }
    }

    // Get subscriptions that are old and are pending payment
    $select = $subscriptionsTable->select()
      ->where('status IN(?)', array('initial', 'pending'))
      ->where('expiration_date <= ?', new Zend_Db_Expr('DATE_SUB(NOW(), INTERVAL 2 DAY)'))
      ->order('subscription_id ASC')
      ->limit(50);

    foreach( $subscriptionsTable->fetchAll($select) as $subscription ) {
      $subscription->onCancel();
      if ($subscription->didStatusChange()) {
        $package = $subscription->getPackage();
        Engine_Api::_()->getApi('mail', 'core')->sendSystem($subscription->getUser(), 'payment_subscription_cancelled', array(
            'subscription_title' => $package->title,
            'queue'=>false,
            'subscription_description' => $package->description,
            'subscription_terms' => $package->getPackageDescription(),
            'object_link' => 'http://' . $_SERVER['HTTP_HOST'] .
            Zend_Controller_Front::getInstance()->getRouter()->assemble(array(), 'user_login', true),
        ));
      }
    }
  }
}


