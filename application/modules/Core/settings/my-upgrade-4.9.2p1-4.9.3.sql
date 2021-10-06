--
-- Adding new column
--

ALTER TABLE `engine4_core_adcampaigns` ADD `target_member` tinyint(4) NOT NULL default '1' AFTER `level`;
