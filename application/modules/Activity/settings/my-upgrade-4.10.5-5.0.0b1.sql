ALTER TABLE `engine4_activity_actions` ADD INDEX(`type`);

ALTER TABLE `engine4_activity_actiontypes` ADD INDEX(`module`);

ALTER TABLE `engine4_activity_actiontypes` ADD INDEX(`displayable`);

ALTER TABLE `engine4_activity_actiontypes` ADD INDEX(`enabled`);

ALTER TABLE `engine4_activity_notifications` ADD INDEX(`type`);

ALTER TABLE `engine4_activity_notifications` ADD INDEX(`read`);

ALTER TABLE `engine4_activity_notifications` ADD INDEX(`mitigated`);

ALTER TABLE `engine4_activity_notificationtypes` ADD INDEX(`module`);

ALTER TABLE `engine4_activity_notificationtypes` ADD INDEX(`default`);