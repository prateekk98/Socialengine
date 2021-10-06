<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Payment
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: content.php 9995 2013-03-26 00:23:47Z alex $
 * @author     John
 */
return array(
    array(
        'title' => 'Membership Subscription',
        'description' => 'This widget will display the details of subscription plan chosen by the member with the  button for membership upgrade. Place this widget on Member Profile Page only.',
        'category' => 'Payment',
        'type' => 'widget',
        'autoEdit' => true,
        'name' => 'payment.subscribe-plan',
        'adminForm' => array(
            'elements' => array(
                array(
                    'MultiCheckbox',
                    'show_criteria',
                    array(
                        'label' => "Details to be shown in this widget:",
                        'multiOptions' => array(
                            'planTitle' => 'Subscription/Plan Title & Description',
                            'expiryDate' => 'Plan Expiry Date (in One Time Plan). “Never Expire” will show if Billing Duration is Forever.',
                            'nextPayment' => 'Next Payment Date (in Recurring Plan)',
                            'currentMember' => 'Current Member Level',
                            'daysleft' => 'Number of Subscription Days Left. (This setting will not show for One Time plans with Forever Billing Duration.)',
                        ),
                    )
                ),
                array(
                    'Radio',
                    'paymentButton',
                    array(
                        'label' => "Do you want to display “Make Payment” button to enable users to make the next payment for the subscribed One Time plan in this widget? (This setting will not show if One Time plans has Forever Billing Duration.)",
                        'multiOptions' => array(
                            '1' => 'Yes',
                            '0' => 'No',
                        ),
                    )
                ),
            ),
        ),
    ),
) ?>
