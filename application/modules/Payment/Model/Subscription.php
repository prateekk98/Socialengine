<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Subscription.php 10098 2013-10-19 00:01:38Z jung $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Core
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Model_Subscription extends Core_Model_Item_Abstract
{
    protected $_searchTriggers = false;

    protected $_modifiedTriggers = false;

    protected $_user;

    protected $_gateway;

    protected $_package;

    protected $_statusChanged;

    public function getUser()
    {
        if( empty($this->user_id) ) {
        return null;
        }
        if( null === $this->_user ) {
        $this->_user = Engine_Api::_()->getItem('user', $this->user_id);
        }
        return $this->_user;
    }

    public function getGateway()
    {
        if( empty($this->gateway_id) ) {
        return null;
        }
        if( null === $this->_gateway ) {
        $this->_gateway = Engine_Api::_()->getItem('payment_gateway', $this->gateway_id);
        }
        return $this->_gateway;
    }

    public function getPackage()
    {
        if( empty($this->package_id) ) {
        return null;
        }
        if( null === $this->_package ) {
        $this->_package = Engine_Api::_()->getItem('payment_package', $this->package_id);
        }
        return $this->_package;
    }

  // Actions

    public function upgradeUser()
    {
        $user = $this->getUser();
        if( !$user ||
            !isset($user->level_id) ||
            !isset($user->enabled) ) {
            return $this;
        }
        $level = $this->getPackage()->getLevel();
        if( !$level ||
            !isset($level->level_id) ) {
        return $this;
        }
        if( $user->level_id != $level->level_id ) {
        $user->level_id = $level->level_id;
        }
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();
        return $this;
    }

    public function downgradeUser()
    {
        $user = $this->getUser();
        if( !$user ||
            !isset($user->level_id) ||
            !isset($user->enabled) ) {
        return $this;
        }
        $package = $this->getPackage();
        if( !$package ||
            !isset($package->downgrade_level_id) ) {
            return $this;
        }
        if(!Engine_Api::_()->getItem('authorization_level', $package->downgrade_level_id))
            return $this;
        if($user->level_id != $package->downgrade_level_id ) {
            $user->level_id = $package->downgrade_level_id;
        }
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();
        return $this;
    }

//     public function downgradeExpirdUser()
//     {
//         $user = $this->getUser();
//         if(!$user ||
//             !isset($user->level_id) ||
//             !isset($user->enabled) ) {
//             return $this;
//         }
//         $package = $this->getPackage();
//         if( !$package ||
//             !isset($package->downgrade_level_id) ) {
//             return $this;
//         }
//         if($user->level_id != $package->downgrade_level_id ) {
//             $user->level_id = $package->downgrade_level_id;
//         }
//         $user->enabled = true; // This will get set correctly in the update hook
//         $user->save();
//         return $this;
//     }

    public function cancel()
    {
        // Try to cancel recurring payments in the gateway
        if( !empty($this->gateway_id) && !empty($this->gateway_profile_id) ) {
        try {
            $gateway = Engine_Api::_()->getItem('payment_gateway', $this->gateway_id);
            if( $gateway ) {
                $gatewayPlugin = $gateway->getPlugin();
                if( method_exists($gatewayPlugin, 'cancelSubscription') ) {
                    $gatewayPlugin->cancelSubscription($this->gateway_profile_id);
                }
            }
        } catch( Exception $e ) {
            // Silence?
        }
        }
        // Cancel this row
        $this->active = false; // Need to do this to prevent clearing the user's session
        $this->onCancel();
        return $this;
    }


    // Active

    public function setActive($flag = true, $deactivateOthers = null)
    {
        $this->active = true;

        if( (true === $flag && null === $deactivateOthers) ||
            $deactivateOthers === true ) {
        $table = $this->getTable();
        $select = $table->select()
            ->where('user_id = ?', $this->user_id)
            ->where('active = ?', true)
            ;
        foreach( $table->fetchAll($select) as $otherSubscription ) {
            $otherSubscription->setActive(false);
        }
        }

        $this->save();
        return $this;
    }



    // Events

    public function clearStatusChanged()
    {
        $this->_statusChanged = null;
        return $this;
    }

    public function didStatusChange()
    {
        return (bool) $this->_statusChanged;
    }

    public function onPaymentSuccess()
    {
        $this->_statusChanged = false;
        if( in_array($this->status, array('initial', 'trial', 'pending', 'active')) ) {

            // If the subscription is in initial or pending, set as active and
            // cancel any other active subscriptions
            if( in_array($this->status, array('initial', 'pending')) ) {
                $this->setActive(true);
                Engine_Api::_()->getDbtable('subscriptions', 'payment')
                ->cancelAll($this->getUser(), 'User cancelled the subscription.', $this);
            }
            if($this->main_package_id){
                $this->package_id = $this->main_package_id;
                $this->save();
            }
            // Update expiration to expiration + recurrence or to now + recurrence?
            $package = $this->getPackage();
            $expiration = $package->getExpirationDate((strtotime($this->expiration_date) > time() ? strtotime($this->expiration_date): time()));
            if( $expiration ) {
                $this->expiration_date = date('Y-m-d H:i:s', $expiration);
            }

            // Change status
            if( $this->status != 'active' ) {
                $this->status = 'active';
                $this->_statusChanged = true;
            }

            // Update user if active
            if( $this->active ) {
                $this->upgradeUser();
            }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();

        return $this;
    }

    public function onPaymentPending()
    {
        $this->_statusChanged = false;
        if( in_array($this->status, array('initial', 'trial', 'pending', 'active')) ) {
        // Change status
        if( $this->status != 'pending' ) {
            $this->status = 'pending';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // @todo should we do this?
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            //Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();
        $levelIdChanged = 0;
        $user = $this->getUser();
        $defaultPackage = Engine_Api::_()->getDbtable('packages', 'payment')->getDefaultPackage();
        if(!empty($defaultPackage) && (int)$defaultPackage->price == 0 && $defaultPackage->package_id != $this->package_id){

            $this->main_package_id = $this->package_id;
            $this->save();
            $package = $this->getPackage();
            if($defaultPackage->level_id) {
                $user->level_id = $defaultPackage->level_id;
                $user->save();  
                $levelIdChanged = 1;
            }

            $this->active = 1;
            $this->status = 'active';
            $this->package_id = $defaultPackage->package_id;
            $this->save();

            // Update expiration to expiration + recurrence or to now + recurrence?
            $expiration = $defaultPackage->getExpirationDate((strtotime($this->expiration_date) > time() ? strtotime($this->expiration_date): time()));
            if( $expiration ) {
                $this->expiration_date = date('Y-m-d H:i:s', $expiration);
            }
        }

        // Check if the member should be enabled
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();
        if(!$levelIdChanged){
            $this->downgradeUser();
            $this->save();
        }
        return $this;
    }

    public function onPaymentFailure()
    {
        $this->_statusChanged = false;
        if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue')) ) {
            // Change status
            if( $this->status != 'overdue' ) {
                $this->status = 'overdue';
                $this->_statusChanged = true;
            }

            // Downgrade and log out user if active
            if( $this->active ) {
                // Downgrade user
                $this->downgradeUser();

                // Remove active sessions?
                Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
            }
        }
        $levelIdChanged = 0;
        // Check if the member should be enabled
        $user = $this->getUser();
        $defaultPackage = Engine_Api::_()->getDbtable('packages', 'payment')->getDefaultPackage();
        if(!empty($defaultPackage) && (int)$defaultPackage->price == 0 && $defaultPackage->package_id != $this->package_id){

            $this->main_package_id = $this->package_id;
            $this->save();
            if($defaultPackage->level_id) {
                $user->level_id = $defaultPackage->level_id;
                $user->save();  
                $levelIdChanged = 1;
            }

            $this->active = 1;
            $this->status = 'active';
            $this->package_id = $defaultPackage->package_id;
            $this->save();

            // Update expiration to expiration + recurrence or to now + recurrence?
            $expiration = $defaultPackage->getExpirationDate((strtotime($this->expiration_date) > time() ? strtotime($this->expiration_date): time()));
            if( $expiration ) {
                $this->expiration_date = date('Y-m-d H:i:s', $expiration);
            }
        }

        $user->enabled = 1; // This will get set correctly in the update hook
        $user->save();
        
        if(!$levelIdChanged){
            $this->downgradeUser();
            $this->save();
        }

        return $this;
    }

    public function onCancel()
    {
        $this->_statusChanged = false;
        if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'overdue', 'cancelled')) ) {
        // Change status
        if( $this->status != 'cancelled' ) {
            $this->status = 'cancelled';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();

        return $this;
    }

    public function onExpiration()
    {
        $this->_statusChanged = false;
        if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'expired', 'overdue')) ) {
        // Change status
        if( $this->status != 'expired' ) {
            $this->status = 'expired';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        if( $user && isset($user->enabled) ) { // Fix for deleted members
            $user->enabled = true; // This will get set correctly in the update hook
            $user->save();
        }

        return $this;
    }

    public function onRefund()
    {
        $this->_statusChanged = false;
        if( in_array($this->status, array('initial', 'trial', 'pending', 'active', 'refunded')) ) {
        // Change status
        if( $this->status != 'refunded' ) {
            $this->status = 'refunded';
            $this->_statusChanged = true;
        }

        // Downgrade and log out user if active
        if( $this->active ) {
            // Downgrade user
            $this->downgradeUser();

            // Remove active sessions?
            Engine_Api::_()->getDbtable('session', 'core')->removeSessionByAuthId($this->user_id);
        }
        }
        $this->save();

        // Check if the member should be enabled
        $user = $this->getUser();
        $user->enabled = true; // This will get set correctly in the update hook
        $user->save();

        return $this;
    }
}
