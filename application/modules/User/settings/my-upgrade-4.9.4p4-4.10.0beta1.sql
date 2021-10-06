-- --------------------------------------------------------

--
-- Cover photo work
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `editable`, `is_generated`) VALUES
('cover_photo_update', 'user', '{item:$subject} has added a new cover photo.', 1, 5, 1, 4, 1, 0, 1);

ALTER TABLE `engine4_users` ADD `coverphoto` INT ( 11 ) NOT NULL DEFAULT '0';

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'user' as `type`,
    'coverphotoupload' as `name`,
    1 as `value`,
    NULL as `params`
FROM `engine4_authorization_levels` WHERE `type` NOT IN('public');
