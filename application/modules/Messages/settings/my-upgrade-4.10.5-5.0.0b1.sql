--
-- indexing for table `engine4_messages_conversations`
--
ALTER TABLE `engine4_messages_conversations` ADD INDEX(`resource_type`);

ALTER TABLE `engine4_messages_conversations` ADD INDEX(`resource_id`);

--
-- indexing for table `engine4_messages_recipients`
--
ALTER TABLE `engine4_messages_recipients` ADD INDEX(`inbox_read`);

ALTER TABLE `engine4_messages_recipients` ADD INDEX(`inbox_deleted`);

ALTER TABLE `engine4_messages_recipients` ADD INDEX(`outbox_deleted`);

UPDATE `engine4_core_menuitems` SET `params` = '{"icon":"far fa-envelope"}' WHERE `engine4_core_menuitems`.`name` = 'core_mini_messages';
