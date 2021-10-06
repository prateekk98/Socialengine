--
-- Dumping data for table `engine4_core_menuitems`
--

INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('authorization_admin_main_level_mapprofiletype', 'authorization', 'Profile Types and Levels Mapping', '', '{"route":"admin_default","module":"authorization","controller":"level","action":"manage-profile-type-mapping"}', 'authorization_admin_main', '', 4);


-- --------------------------------------------------------
--
-- Table structure for table `engine4_authorization_mapprofiletypelevels`
--

DROP TABLE IF EXISTS `engine4_authorization_mapprofiletypelevels`;
CREATE TABLE IF NOT EXISTS `engine4_authorization_mapprofiletypelevels` (
  `mapprofiletypelevel_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(256) NOT NULL,
  `description` text NOT NULL,
  `profile_type_id` int(11) NOT NULL,
  `member_level_id` int(11) NOT NULL,
  `member_count` int(11) NOT NULL,
  PRIMARY KEY (`mapprofiletypelevel_id`)
) ENGINE = InnoDB CHARSET=utf8 COLLATE utf8_unicode_ci AUTO_INCREMENT = 1;
