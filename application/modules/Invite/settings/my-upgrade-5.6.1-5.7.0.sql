INSERT IGNORE INTO `engine4_core_menuitems` (`name`, `module`, `label`, `plugin`, `params`, `menu`, `submenu`, `order`) VALUES
('core_admin_main_manage_invites', 'user', 'Manage Invites', '', '{"route":"admin_default","module":"invite","controller":"manage"}', 'core_admin_main_manage', '', 7);

INSERT IGNORE INTO `engine4_activity_notificationtypes` (`type`, `module`, `body`, `is_request`, `handler`, `default`) VALUES
("invite_notify_admin", "invite", '{var:$userprofilelink} has requested to {var:$adminsidelink} the invite request for {var:$recipientemail}.', 0, "", 1);


