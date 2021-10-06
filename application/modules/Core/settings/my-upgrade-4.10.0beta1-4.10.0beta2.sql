-- Hashtag Work

ALTER TABLE `engine4_core_tags` ADD (
  `tag_count` int(11) NOT NULL DEFAULT '0',
  `modified_date` datetime DEFAULT NULL
);