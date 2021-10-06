
--
-- indexing for table `engine4_authorization_levels`
--
ALTER TABLE `engine4_authorization_levels` ADD INDEX(`type`);

ALTER TABLE `engine4_authorization_levels` ADD INDEX(`flag`);

--
-- indexing for table `engine4_authorization_mapprofiletypelevels`
--
ALTER TABLE `engine4_authorization_mapprofiletypelevels` ADD INDEX(`profile_type_id`);

ALTER TABLE `engine4_authorization_mapprofiletypelevels` ADD INDEX(`member_level_id`);

