--
-- Dumping data for table `engine4_core_settings`
--

INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('storage.service.mirrored.counter', '0'),
('storage.service.mirrored.index', '0'),
('storage.service.roundrobin.counter', '0');

--
-- indexing for table `engine4_storage_files`
--
ALTER TABLE `engine4_storage_files` ADD INDEX(`creation_date`);
ALTER TABLE `engine4_storage_files` ADD INDEX(`modified_date`);
ALTER TABLE `engine4_storage_files` ADD INDEX(`mime_major`);
ALTER TABLE `engine4_storage_files` ADD INDEX(`mime_minor`);
ALTER TABLE `engine4_storage_files` ADD INDEX(`extension`);

--
-- indexing for table `engine4_storage_services`
--
ALTER TABLE `engine4_storage_services` ADD INDEX(`servicetype_id`);
ALTER TABLE `engine4_storage_services` ADD INDEX(`enabled`);
ALTER TABLE `engine4_storage_services` ADD INDEX(`default`);

--
-- indexing for table `engine4_storage_servicetypes`
--
ALTER TABLE `engine4_storage_servicetypes` ADD INDEX(`enabled`);