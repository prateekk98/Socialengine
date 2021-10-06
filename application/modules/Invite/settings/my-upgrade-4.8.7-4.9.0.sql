--
-- removing icons image from 'Quick Links' widget
--

UPDATE `engine4_core_menuitems` SET `params`= '{"route":"default","module":"invite"}' where `name`= 'user_home_invite';

--
-- Update `param` column in `engine4_core_menuitems` table
--

UPDATE `engine4_core_menuitems`
SET `params` = '{"route":"default","module":"invite","icon":"fa-envelope"}'
WHERE `name` = 'core_main_invite';
