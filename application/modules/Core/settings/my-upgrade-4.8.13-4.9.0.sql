--
-- updating default doctype
--

UPDATE `engine4_core_settings` SET `value`='HTML5' WHERE `name`='core.doctype';


-- --------------------------------------------------------

--
-- Table structure for table `engine4_core_banners`
--

DROP TABLE IF EXISTS `engine4_core_banners`;
CREATE TABLE IF NOT EXISTS `engine4_core_banners` (
  `banner_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `module` varchar(32) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL,
  `title` varchar(64) NOT NULL,
  `body` varchar(255) NOT NULL,
  `photo_id` int(11) unsigned NOT NULL default '0',
  `params` text NOT NULL,
  `custom` tinyint(1) NOT NULL default '0',
  PRIMARY KEY (`banner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_unicode_ci;

--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_layout_banners', 'core', 'Banner Manager', '', '{"route":"admin_default","controller":"banners"}', 'core_admin_main_layout', '', 6);

--
-- Change label for Layout menu `engine4_core_menuitems`
--

UPDATE `engine4_core_menuitems`
SET `label` = 'Appearance'
WHERE `engine4_core_menuitems`.`name` = 'core_admin_main_layout';

--
-- Add  `params` column in table `engine4_core_links`
--

ALTER TABLE `engine4_core_links` ADD `params` TEXT NULL DEFAULT NULL AFTER `search`;

--
-- Dumping data for table `engine4_core_menuitems`
--
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_settings_iframely', 'core', 'Iframely Integration', '', '{"route":"admin_default","controller":"iframely"}', 'core_admin_main_settings', '', 11),
('core_mini_update', 'activity', 'Updates', 'Activity_Plugin_Menus', '', 'core_mini', '', 100)
;

--
-- Reverse mini menu items ordering
--

UPDATE `engine4_core_menuitems` SET `order`=1000-`order` WHERE `menu`= 'core_mini';

--
-- Dumping data for table `engine4_core_menus`
--

INSERT IGNORE INTO `engine4_core_menus` (`name`, `type`, `title`, `order`) VALUES
('core_social_sites', 'standard', 'Social Site Links Menu', 5)
;

--
-- Dumping data for table `engine4_core_menuitems`
--
INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `enabled`, `custom`, `order`) VALUES
('core_social_site_facebook', 'core', 'Facebook', '', '{"uri": "","target":"_blank", "icon":"fa-facebook"}', 'core_social_sites', 0, 1, 1),
('core_social_site_twitter', 'core', 'Twitter', '', '{"uri": "","target":"_blank", "icon":"fa-twitter"}', 'core_social_sites', 0, 1, 2),
('core_social_site_linkedin', 'core', 'Linkedin', '', '{"uri": "","target":"_blank", "icon":"fa-linkedin"}', 'core_social_sites', 0, 1, 3),
('core_social_site_youtube', 'core', 'Youtube', '', '{"uri": "","target":"_blank", "icon":"fa-youtube"}', 'core_social_sites', 0, 1, 4),
('core_social_site_googleplus', 'core', 'Google +', '', '{"uri": "","target":"_blank", "icon":"fa-google-plus"}', 'core_social_sites', 0, 1, 5),
('core_social_site_pinterest', 'core', 'Pinterest', '', '{"uri": "","target":"_blank", "icon":"fa-pinterest"}', 'core_social_sites', 0, 1, 6)
;

--
-- Dumping data for table `engine4_core_content`
--
INSERT IGNORE INTO `engine4_core_content` (`content_id`, `page_id`, `type`, `name`, `parent_content_id`, `order`, `params`) VALUES
(211, 2, 'widget', 'core.menu-social-sites', 200, 3, '')
;

--
-- Update `param` column in `engine4_core_menuitems` table
--

UPDATE `engine4_core_menuitems`
SET `params` = '{"icon":"fa-home"}'
WHERE `name` = 'core_main_home';

--
-- Dumping data for table `engine4_core_settings`
--
INSERT IGNORE INTO `engine4_core_settings` (`name`, `value`) VALUES
('core.iframely.host', 'socialengine')
;
