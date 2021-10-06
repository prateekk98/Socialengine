
UPDATE `engine4_activity_notificationtypes` SET `body`='Your subscription plan {var:$planName} is going to expire soon on {var:$period}.' WHERE `type` = 'payment_subscription_expiredsoon';

UPDATE `engine4_core_tasks` SET `timeout`= 21600 WHERE `plugin`='Payment_Plugin_Task_Cleanup';
