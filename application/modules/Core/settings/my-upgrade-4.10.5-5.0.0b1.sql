--
-- indexing for table `engine4_core_auth`
--
ALTER TABLE `engine4_core_auth` ADD INDEX(`type`);

--
-- indexing for table `engine4_core_banners`
--
ALTER TABLE `engine4_core_banners` ADD INDEX(`module`);

--
-- indexing for table `engine4_core_content`
--
ALTER TABLE `engine4_core_content` ADD INDEX(`name`);

--
-- indexing for table `engine4_core_jobtypes`
--
ALTER TABLE `engine4_core_jobtypes` ADD INDEX(`module`);

ALTER TABLE `engine4_core_jobtypes` ADD INDEX(`enabled`);

--
-- indexing for table `engine4_core_links`
--
ALTER TABLE `engine4_core_links` ADD INDEX(`creation_date`);

ALTER TABLE `engine4_core_links` ADD INDEX(`search`);

--
-- indexing for table `engine4_core_mailrecipients`
--
ALTER TABLE `engine4_core_mailrecipients` ADD INDEX(`mail_id`);

ALTER TABLE `engine4_core_mailrecipients` ADD INDEX(`user_id`);

--
-- indexing for table `engine4_core_menuitems`
--
ALTER TABLE `engine4_core_menuitems` ADD INDEX(`module`);
ALTER TABLE `engine4_core_menuitems` ADD INDEX(`enabled`);

--
-- indexing for table `engine4_core_modules`
--
ALTER TABLE `engine4_core_modules` ADD INDEX(`enabled`);

--
-- indexing for table `engine4_core_menuitems`
--
ALTER TABLE `engine4_core_modules` ADD INDEX(`enabled`);

--
-- indexing for table `engine4_core_status`
--
ALTER TABLE `engine4_core_status` ADD INDEX(`resource_type`);

ALTER TABLE `engine4_core_status` ADD INDEX(`resource_id`);

UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"far fa-bell"}' WHERE `engine4_core_menuitems`.`name` = 'core_mini_update';
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"far fa-user"}' WHERE `engine4_core_menuitems`.`name` = 'core_mini_admin';
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fas fa-cog"}' WHERE `engine4_core_menuitems`.`name` = 'core_mini_settings';
UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"fas fa-tools"}' WHERE `engine4_core_menuitems`.`name` = 'core_mini_admin';

DROP TABLE IF EXISTS `engine4_core_files`; 
CREATE TABLE `engine4_core_files` ( 
  `file_id` int(10) unsigned NOT NULL auto_increment, 
  `name` varchar(255) default NULL, 
  `storage_path` varchar(255) NOT NULL, 
  `extension` varchar(8) NOT NULL, 
  `storage_file_id` int(10) unsigned NULL, 
  `creation_date` datetime NOT NULL, 
  `modified_date` datetime NOT NULL, 
  PRIMARY KEY  (`file_id`) 
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci ;
