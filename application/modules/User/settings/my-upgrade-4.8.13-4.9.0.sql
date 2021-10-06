--
-- Dumping data for table `engine4_activity_actiontypes`
--

INSERT IGNORE INTO `engine4_activity_actiontypes` (`type`, `module`, `body`, `enabled`, `displayable`, `attachable`, `commentable`, `shareable`, `is_generated`) VALUES
('comment_user', 'user', '{item:$subject} commented on {item:$owner}''s profile: {body:$body}', 1, 7, 1, 3, 1, 1);

UPDATE `engine4_activity_actiontypes` SET `editable` = '1' WHERE `engine4_activity_actiontypes`.`type` in ('status', 'post', 'post_self');

--
-- Adding and populating `like_count` column
--

ALTER TABLE `engine4_users` ADD `like_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `view_count`;

UPDATE `engine4_users` SET `like_count`=(SELECT COUNT('like_id')
                                                  FROM `engine4_core_likes`
                                                  WHERE `resource_type`='user' and `resource_id`=`engine4_users`.`user_id`
                                               );

--
-- Adding and populating `comment_count` column
--

ALTER TABLE `engine4_users` ADD `comment_count` INT(11) UNSIGNED NOT NULL DEFAULT '0' AFTER `view_count`;

UPDATE `engine4_users` SET `comment_count`=(SELECT COUNT('comment_id')
                                                  FROM `engine4_core_comments`
                                                  WHERE `resource_type`='user' and `resource_id`=`engine4_users`.`user_id`
                                               );

--
-- Dumping data for table `engine4_core_banners`
--

INSERT IGNORE INTO `engine4_core_banners`(`name`, `module`, `title`, `body`, `photo_id`, `params`, `custom`) VALUES
('user', 'user', 'Connect with People', 'The world is a book. Those who do not connect with others miss many pages.', 0, '{"label":"Invite","route":"default","routeParams":{"module":"invite"}}', 0);

--
-- removing icons image from 'Quick Links' widget
--

UPDATE `engine4_core_menuitems` SET `params`= '{"route":"recent_activity"}' where `name`= 'user_home_updates';
UPDATE `engine4_core_menuitems` SET `params`= '{"route":"user_profile_self"}' where `name`= 'user_home_view';
UPDATE `engine4_core_menuitems` SET `params`= '{"route":"user_extended"}' where `name`= 'user_home_edit';
UPDATE `engine4_core_menuitems` SET `params`= '{"route":"user_general","controller":"index","action":"browse"}' where `name`= 'user_home_friends';

--
-- Update `param` column in `engine4_core_menuitems` table
--

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"user_general","action":"browse","icon":"fa-user"}'
WHERE `name` = 'core_main_user';
