--
-- Dumping data for table `engine4_core_tasks`
--

INSERT IGNORE INTO `engine4_core_tasks` (`title`, `module`, `plugin`, `timeout`) VALUES
('Session Maintenance', 'core', 'Core_Plugin_Task_Cleanup', 86400);
