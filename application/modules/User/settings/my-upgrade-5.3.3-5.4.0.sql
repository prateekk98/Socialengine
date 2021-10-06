ALTER TABLE `engine4_users` ADD COLUMN `birthday_format` VARCHAR(24) DEFAULT NULL;

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'allow_birthday' as `name`,
    1 as `value`,
    NULL as `params`
  FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');

INSERT IGNORE INTO `engine4_authorization_permissions`
SELECT
  level_id as `level_id`,
  'user' as `type`,
  'birthday_options' as `name`,
  5 as `value`,
  '["monthday","monthdayyear"]' as `params`
FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
