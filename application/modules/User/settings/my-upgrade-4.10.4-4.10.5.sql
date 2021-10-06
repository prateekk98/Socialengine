--
-- Adding and populating `disable_email` column
--

 ALTER TABLE `engine4_users` ADD `disable_email` TINYINT(1) NOT NULL DEFAULT '0';
 ALTER TABLE `engine4_users` ADD `disable_adminemail` TINYINT(1) NOT NULL DEFAULT '0';

ALTER TABLE `engine4_users` ADD `last_login_attempt` DATETIME NULL;
ALTER TABLE `engine4_users` ADD `login_attempt_count` INT(5) NOT NULL DEFAULT '0';

ALTER TABLE `engine4_users` CHANGE `password` `password` CHAR(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL;
ALTER TABLE `engine4_users` ADD `last_password_reset` DATETIME NULL;
