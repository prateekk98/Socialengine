-- --------------------------------------------------------

--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`,  `body`,  `enabled`,  `displayable`,  `attachable`,  `commentable`,  `shareable`, `is_generated`) VALUES
('like_activity_action', 'activity', '{item:$subject} liked {item:$owner}''s {item:$object:post}.', 1, 1, 1, 3, 3, 0),
('comment_activity_action', 'activity', '{item:$subject} commented {item:$owner}''s {item:$object:post}.', 1, 1, 1, 3, 3, 0);

/* add  editable column in engine4_activity_actiontypes*/
ALTER TABLE `engine4_activity_actiontypes` ADD `editable` tinyint(1) NOT NULL default '0';

UPDATE `engine4_activity_actiontypes` SET `editable` = '1' WHERE `engine4_activity_actiontypes`.`type` = 'share';

/* add  modified_date column in engine4_activity_actions*/
ALTER TABLE `engine4_activity_actions` ADD `modified_date` datetime NOT NULL;

--
-- Dumping data for table `engine4_authorization_permissions`
--


-- ADMIN, MODERATOR, USER
-- activity - edit_time

INSERT IGNORE INTO `engine4_authorization_permissions`
  SELECT
    level_id as `level_id`,
    'activity' as `type`,
    'edit_time' as `name`,
    3 as `value`,
    0 as `params`
  FROM `engine4_authorization_levels` WHERE `type` IN('admin', 'moderator', 'user');
