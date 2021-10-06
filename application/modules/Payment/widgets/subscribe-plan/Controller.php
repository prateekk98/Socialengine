<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Extensions
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Payment_Widget_SubscribePlanController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        $user = Engine_Api::_()->core()->getSubject('user');
        // Check if they are an admin or moderator (don't require subscriptions from them)
        $this->view->level = $level = Engine_Api::_()->getItem('authorization_level', $user->level_id);
        if(in_array($level->type, array('admin', 'moderator')) ) {
                return $this->setNoRender();
        }
        $showCriteria = $this->_getParam('show_criteria',array('planTitle','expiryDate','nextPayment','currentMember','daysleft'));
        foreach ($showCriteria as $show_criteria)
            $this->view->{$show_criteria . 'Active'} = $show_criteria;
        $this->view->paymentButton = $this->_getParam('paymentButton',1);
        if(!$showCriteria &&  !$this->view->paymentButton )
            return $this->setNoRender();

        $packagesTable = Engine_Api::_()->getDbtable('packages', 'payment');

        $currentSubscription = array();
        // Get current subscription and package
        $subscriptionsTable = Engine_Api::_()->getDbtable('subscriptions', 'payment');
        $this->view->currentSubscription = $currentSubscription = $subscriptionsTable->fetchRow(array(
            'user_id = ?' => $user->getIdentity(),
            'active = ?' => true,
        ));
        $gateway = Engine_Api::_()->getDbtable('gateways', 'payment')->find($currentSubscription->gateway_id)->current();
        $this->view->isGatewayEnabled = 0;
        if($gateway){
            if($gateway->enabled)
                $this->view->isGatewayEnabled = 1;
        }
        // Get current package
        if( $currentSubscription ) {
            $this->view->currentPackage = $currentPackage = $packagesTable->fetchRow(array(
                'package_id = ?' => $currentSubscription->package_id,
            ));
        }
        if(@!count($currentSubscription) || @!count($currentPackage))
            return $this->setNoRender();
    }
}
