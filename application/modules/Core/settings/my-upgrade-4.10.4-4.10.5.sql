
--
-- Insert new entry
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('user_settings_emails', 'user', 'Emails', '', '{"route":"user_extended","module":"user","controller":"settings","action":"emails"}', 'user_settings', '', 4),
('core_admin_main_settings_emails', 'user', 'Default Email Alerts', '', '{"route":"admin_default","module":"user","controller":"settings","action":"emails"}', 'core_admin_main_settings', '', 12);

UPDATE `engine4_core_menuitems` SET `order` = '13' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_settings_storage';
UPDATE `engine4_core_menuitems` SET `order` = '5' WHERE `engine4_core_menuitems`.`name` = 'user_settings_network';
-- --------------------------------------------------------

--
-- Table structure for table `engine4_user_emailsettings`
--

DROP TABLE IF EXISTS `engine4_user_emailsettings`;
CREATE TABLE IF NOT EXISTS `engine4_user_emailsettings` (
  `user_id` int(11) unsigned NOT NULL,
  `type` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `email` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`user_id`,`type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;


--
-- Update entry
--

UPDATE `engine4_core_menuitems` SET `label` = 'Default Notification Alerts' WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_settings_notifications';


--
-- Adding new column
--

ALTER TABLE `engine4_core_mailtemplates` ADD `default` TINYINT(1) NOT NULL DEFAULT "1";
