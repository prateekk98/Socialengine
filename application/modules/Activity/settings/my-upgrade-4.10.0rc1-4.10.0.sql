UPDATE `engine4_activity_actiontypes`
SET `body` = '{item:$subject} commented on {item:$owner}''s {item:$object:post}.'
WHERE `type` = 'comment_activity_action';
