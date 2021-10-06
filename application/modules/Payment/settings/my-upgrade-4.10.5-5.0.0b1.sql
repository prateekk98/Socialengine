INSERT IGNORE INTO `engine4_payment_gateways` (`title`, `description`, `enabled`, `plugin`, `test_mode`) VALUES
('Free', NULL, 0, 'Payment_Plugin_Gateway_Free', 0),
('Bank', NULL, 0, 'Payment_Plugin_Gateway_Bank', 0),
('Cash', NULL, 0, 'Payment_Plugin_Gateway_Cash', 0),
('Cheque', NULL, 0, 'Payment_Plugin_Gateway_Cheque', 0);

ALTER TABLE `engine4_payment_transactions` ADD `params` VARCHAR(255) NULL;
ALTER TABLE `engine4_payment_transactions` ADD `file_id` INT NULL;

INSERT IGNORE INTO `engine4_core_mailtemplates` (`type`, `module`, `vars`) VALUES
('payment_subscription_refunded', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link]'),
('payment_subscription_transaction', 'payment', '[host],[email],[recipient_title],[recipient_link],[recipient_photo],[subscription_title],[subscription_description],[object_link],[gateway_type],[attechment]');
