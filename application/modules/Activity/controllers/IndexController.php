<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: IndexController.php 10194 2014-05-01 17:41:40Z mfeineman $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_IndexController extends Core_Controller_Action_Standard
{

    /**
     * Handles HTTP POST requests to create an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/post
     *
     * If URL acccessed directly, the follwoing view script is use:
     *  - /Activity/views/scripts/index/post.tpl
     *
     * @return void
     */
    public function postAction()
    {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        // Get subject if necessary
        $viewer = Engine_Api::_()->user()->getViewer();
        $subject = null;
        $subject_guid = $this->_getParam('subject', null);
        if ($subject_guid) {
            $subject = Engine_Api::_()->getItemByGuid($subject_guid);
        }
        // Use viewer as subject if no subject
        if (null === $subject) {
            $subject = $viewer;
        }

        // Make form
        $form = $this->view->form = new Activity_Form_Post();

        // Check auth
        if (!$subject->authorization()->isAllowed($viewer, 'comment')) {
            return $this->_helper->requireAuth()->forward();
        }

        // Check if post
        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not post');
            return;
        }

        // Check token
        if (!($token = $this->_getParam('token'))) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('No token, please try again');
            return;
        }

        if (!in_array($token, $this->view->activityFormToken()->getTokens())) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid token, please try again');
            return;
        }

        // Check if form is valid
        $postData = $this->getRequest()->getPost();
        $body = @$postData['body'];
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        //$body = htmlentities($body, ENT_QUOTES, 'UTF-8');
        $postData['body'] = $body;

        if (!$form->isValid($postData)) {
            $this->view->status = false;
            $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        // Check one more thing
        if ($form->body->getValue() === '' && $form->getValue('attachment_type') === '') {
            $this->view->status = false;
            $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        $activityFlood = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $this->view->viewer()->level_id, 'activity_flood');

        if(!empty($activityFlood[0])){
            //get last activity
            $tableFlood = Engine_Api::_()->getDbTable("actions",'activity');
            $select = $tableFlood->select()->where("subject_id = ?",$this->view->viewer()->getIdentity())->order("date DESC");
            if($activityFlood[1] == "minute"){
                $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 MINUTE)");
            }else if($activityFlood[1] == "day"){
                $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 DAY)");
            }else{
                $select->where("date >= DATE_SUB(NOW(),INTERVAL 1 HOUR)");
            }
            $floodItem = $tableFlood->fetchAll($select);
            if(count($floodItem) && $activityFlood[0] <= count($floodItem)){
                $message = Engine_Api::_()->core()->floodCheckMessage($activityFlood,$this->view);
                $this->view->flood = true;
                $this->view->status = true;
                $this->view->floodMessage =  $message;
                return;
            }
        }


        // set up action variable
        $action = null;

        // Process
        $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            // Get body
            $body = $form->getValue('body');
            $body = preg_replace('/<br[^<>]*>/', "\n", $body);
            // Try attachment getting stuff
            $attachment = $attachments = null;
            $attachmentCount = 0;
            $attachmentData = $this->getRequest()->getParam('attachment');
            $privacy = isset($postData['auth_view']) ? @$postData['auth_view'] :
                Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.content', 'everyone');

            if (!empty($attachmentData) && !empty($attachmentData['type'])) {
                $type = $attachmentData['type'];
                $config = null;
                foreach (Zend_Registry::get('Engine_Manifest') as $data) {
                    if (!empty($data['composer'][$type])) {
                        $config = $data['composer'][$type];
                    }
                }
                if (!empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1])) {
                    $config = null;
                }
                if ($config) {
                    $attachmentData['actionBody'] = $body;
                    $plugin = Engine_Api::_()->loadClass($config['plugin']);
                    $method = 'onAttach'.ucfirst($type);
                    $attachments = $attachment = $plugin->$method($attachmentData);
                    if (!is_array($attachments)) {
                        $attachments = array($attachment);
                    }
                    $attachmentCount = count($attachments);
                }
            }


            // Is double encoded because of design mode
            //$body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
            //$body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
            //$body = htmlentities($body, ENT_QUOTES, 'UTF-8');

            // Special case: status
            if (!$attachmentCount && $viewer->isSelf($subject)) {
                if ($body != '') {
                    $viewer->status = $body;
                    $viewer->status_date = date('Y-m-d H:i:s');
                    $viewer->save();

                    $viewer->status()->setStatus($body);
                }

                $action = Engine_Api::_()->getDbtable('actions', 'activity')
                    ->addActivity($viewer, $subject, 'status', $body, array('privacy' => $privacy));
            } else { // General post

                $activityType = 'post';
                if ($viewer->isSelf($subject)) {
                    $activityType = 'post_self';
                } else {
                    $postActionType = 'post_' . $subject->getType();
                    $actionType = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($postActionType);
                    if ($actionType) {
                        $activityType = $postActionType;
                    }
                }


                if ($attachmentCount > 1) {
                    $postActionType = $activityType . '_multi_' . $type;
                    $actionType = Engine_Api::_()->getDbtable('actionTypes', 'activity')->getActionType($postActionType);
                    $activityType =  !empty($actionType) ? $postActionType : $activityType;
                }

                // Add notification for <del>owner</del> user
                $subjectOwner = $subject->getOwner();

                if (!$viewer->isSelf($subject) &&
                    $subject instanceof User_Model_User) {
                    $notificationType = 'post_'.$subject->getType();
                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($subjectOwner, $viewer, $subject, $notificationType, array(
                        'url1' => $subject->getHref(),
                    ));
                }

                // Add activity
                $actionTable = Engine_Api::_()->getDbtable('actions', 'activity');
                $action = $actionTable->addActivity($viewer, $subject, $activityType, $body, array(
                    'count' => $attachmentCount,
                    'privacy' => $privacy
                ));

                // Try to attach if necessary
                if ($action && $attachmentCount) {
                    // Item Privacy Work Start
                    if (!empty($privacy)) {
                        if (!in_array($privacy, array('everyone', 'networks', 'friends', 'onlyme'))) {
                            $privacy = Engine_Api::_()->activity()->isNetworkBasePrivacy($privacy) ? 'networks' : 'onlyme';
                        }
                        foreach($attachments as $attachmentItem) {
                            Engine_Api::_()->activity()->editContentPrivacy($attachmentItem, $viewer, $privacy);
                        }
                    }

                    $mode = $attachmentCount > 1 ? Activity_Model_Action::ATTACH_MULTI : Activity_Model_Action::ATTACH_NORMAL ;
                    foreach ($attachments as $attachmentItem) {
                        $actionTable->attachActivity($action, $attachmentItem, $mode);
                    }
                    $attachment = $attachments[0];
                }
            }

            $composerDatas = $this->getRequest()->getParam('composer', null);
            if ($action && !empty($composerDatas['tag'])) {
                $tagsArray = array();
                parse_str($composerDatas['tag'], $tagsArray);

                if (!empty($tagsArray)) {
                    $action->params = array_merge((array) $action->params, array('tags' => $tagsArray));
                    $action->save();
                    $this->sendTagNotification($tagsArray, $action);
                }
            }

            // Preprocess attachment parameters
            $publishMessage = html_entity_decode($form->getValue('body'));
            $publishUrl = null;
            $publishName = null;
            $publishDesc = null;
            $publishPicUrl = null;
            // Add attachment
            if ($attachment) {
                $publishUrl = $attachment->getHref();
                $publishName = $attachment->getTitle();
                $publishDesc = $attachment->getDescription();
                if (empty($publishName)) {
                    $publishName = ucwords($attachment->getShortType());
                }
                if (($tmpPicUrl = $attachment->getPhotoUrl())) {
                    $publishPicUrl = $tmpPicUrl;
                }
                // prevents OAuthException: (#100) FBCDN image is not allowed in stream
                if ($publishPicUrl &&
                    preg_match('/fbcdn.net$/i', parse_url($publishPicUrl, PHP_URL_HOST))) {
                    $publishPicUrl = null;
                }
            } else {
                $publishUrl = !$action ? null : $action->getHref();
            }
            // Check to ensure proto/host
            if ($publishUrl &&
                false === stripos($publishUrl, 'http://') &&
                false === stripos($publishUrl, 'https://')) {
                $publishUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishUrl;
            }
            if ($publishPicUrl &&
                false === stripos($publishPicUrl, 'http://') &&
                false === stripos($publishPicUrl, 'https://')) {
                $publishPicUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishPicUrl;
            }
            // Add site title
            if ($publishName) {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title
                    . ": " . $publishName;
            } else {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title;
            }


            // Publish to twitter, if checked & enabled
            if ($this->_getParam('post_to_twitter', false) &&
                'publish' == Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable) {
                try {
                    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
                    if ($twitterTable->isConnected()) {
                        // @todo truncation?
                        // @todo attachment
                        $twitter = $twitterTable->getApi();
                        $twitter->statuses->update($publishMessage);
                    }
                } catch (Exception $e) {
                    // Silence
                }
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e; // This should be caught by error handler
        }

        // If we're here, we're done
        $this->view->status = true;
        $this->view->message =  Zend_Registry::get('Zend_Translate')->_('Success!');

        // Check if action was created
        $post_fail = "";
        if (!$action) {
            $this->view->status = false;
            $post_fail = "?pf=1";
        }

        // Redirect if in normal context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $return_url = $form->getValue('return_url', false);
            if ($return_url) {
                return $this->_helper->redirector->gotoUrl($return_url.$post_fail, array('prependBase' => false));
            }
        } else {
            $this->view->activityFormToken()->unsetToken($token);
            $this->view->formToken = $this->view->activityFormToken()->createToken();
            $this->view->action_id = $action->action_id;
            $this->_helper->contextSwitch->initContext();
        }
    }

    /**
     * Handles HTTP request to get an activity feed item's likes and returns a
     * Json as the response
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/viewlike
     *
     * @return void
     */
    public function viewlikeAction()
    {
        // Collect params
        $action_id = $this->_getParam('action_id');
        $viewer = Engine_Api::_()->user()->getViewer();

        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);


        // Redirect if not json context
        if (null === $this->_getParam('format', null)) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } elseif ('json' === $this->_getParam('format', null)) {
            $this->view->body = $this->view->activity($action, array('viewAllLikes' => true, 'noList' => $this->_getParam('nolist', false)));
        }
    }

    /**
     * Handles HTTP request to like an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/like
     *   *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function likeAction()
    {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        // Collect params
        $action_id = $this->_getParam('action_id');
        $comment_id = $this->_getParam('comment_id');
        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('You now like this action.');

        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);

            // Action
            if (!$comment_id) {
                // Check authorization
                if ($action && !Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')) {
                    throw new Engine_Exception('This user is not allowed to like this item');
                }

                $action->likes()->addLike($viewer);

                // Add notification for owner of activity (if user and not viewer)
                if ($action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity()) {
                    $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);

                    Engine_Api::_()->getDbtable('notifications', 'activity')->addNotification($actionOwner, $viewer, $action, 'liked', array(
                        'label' => 'post'
                    ));
                }
            } else {
                $comment = $action->comments()->getComment($comment_id);

                // Check authorization
                if (!$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment')) {
                    throw new Engine_Exception('This user is not allowed to like this item');
                }

                $comment->likes()->addLike($viewer);

                // @todo make sure notifications work right
                if ($comment->poster_id != $viewer->getIdentity()) {
                    Engine_Api::_()->getDbtable('notifications', 'activity')
                        ->addNotification($comment->getPoster(), $viewer, $comment, 'liked', array(
                            'label' => 'comment'
                        ));
                }

                // Add notification for owner of activity (if user and not viewer)
                if ($action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity()) {
                    $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
                }
            }

            // Stats
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.likes');

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            $this->view->status = false;
            $this->view->message = $e->getMessage();
        }

        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } elseif ('json'===$this->_helper->contextSwitch->getCurrentContext()) {
            $method = 'update';
            $this->view->body = $this->view->activity($action, array('noList' => true), $method);
        }
    }

    /**
     * Handles HTTP request to remove a like from an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/unlike
     *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function unlikeAction()
    {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        // Collect params
        $action_id = $this->_getParam('action_id');
        $comment_id = $this->_getParam('comment_id');
        $viewer = Engine_Api::_()->user()->getViewer();

        // Start transaction
        $db = Engine_Api::_()->getDbtable('likes', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);

            // Action
            if (!$comment_id) {

                // Check authorization
                if (!Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')) {
                    throw new Engine_Exception('This user is not allowed to unlike this item');
                }

                $action->likes()->removeLike($viewer);
            }

            // Comment
            else {
                $comment = $action->comments()->getComment($comment_id);

                // Check authorization
                if (!$comment || !Engine_Api::_()->authorization()->isAllowed($comment->getAuthorizationItem(), null, 'comment')) {
                    throw new Engine_Exception('This user is not allowed to like this item');
                }

                $comment->likes()->removeLike($viewer);
            }

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Success
        $this->view->status = true;
        $this->view->message = Zend_Registry::get('Zend_Translate')->_('You no longer like this action.');

        // Redirect if not json context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } elseif ('json'===$this->_helper->contextSwitch->getCurrentContext()) {
            $method = 'update';
            $this->view->body = $this->view->activity($action, array('noList' => true), $method);
        }
    }

    /**
     * Handles HTTP request to get an activity feed item's comments and returns
     * a Json as the response
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/viewcomment
     *
     * @return void
     */
    public function viewcommentAction()
    {
        // Collect params
        $action_id = $this->_getParam('action_id');
        $viewer    = Engine_Api::_()->user()->getViewer();

        $action    = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
        $form      = $this->view->form = new Activity_Form_Comment();
        $form->setActionIdentity($action_id);


        // Redirect if not json context
        if (null===$this->_getParam('format', null)) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } elseif ('json'===$this->_getParam('format', null)) {
            $this->view->body = $this->view->activity($action, array('viewAllComments' => true, 'noList' => $this->_getParam('nolist', false)));
        }
    }

    /**
     * Handles HTTP POST request to comment on an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/comment
     *
     * @throws Engine_Exception If a user lacks authorization
     * @return void
     */
    public function commentAction()
    {
        // Make sure user exists
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        // Make form
        $this->view->form = $form = new Activity_Form_Comment();

        // Not post
        if (!$this->getRequest()->isPost()) {
            $this->view->status = false;
            $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Not a post');
            return;
        }

        // Not valid
        if (!$form->isValid($this->getRequest()->getPost())) {
            $this->view->status = false;
            $this->view->error =  Zend_Registry::get('Zend_Translate')->_('Invalid data');
            return;
        }

        // Start transaction
        $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            $viewer = Engine_Api::_()->user()->getViewer();
            $action_id = $this->view->action_id = $this->_getParam('action_id', $this->_getParam('action', null));
            $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
            if (!$action) {
                $this->view->status = false;
                $this->view->error  = Zend_Registry::get('Zend_Translate')->_('Activity does not exist');
                return;
            }
            $actionOwner = Engine_Api::_()->getItemByGuid($action->subject_type."_".$action->subject_id);
            $body = $form->getValue('body');
            $bodyEmojis = explode(' ', $body);
            foreach($bodyEmojis as $bodyEmoji) {
                $emojisCode = Engine_Text_Emoji::encode($bodyEmoji);
                $body = str_replace($bodyEmoji,$emojisCode,$body);
            }
            // Check authorization
            if (!Engine_Api::_()->authorization()->isAllowed($action->getCommentableItem(), null, 'comment')) {
                throw new Engine_Exception('This user is not allowed to comment on this item.');
            }

            // Add the comment
            $commentRow = $action->comments()->addComment($viewer, $body);
            $this->addHashtags($commentRow);

            // Notifications
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');

            // Add notification for owner of activity (if user and not viewer)
            if ($action->subject_type == 'user' && $action->subject_id != $viewer->getIdentity()) {
                $notifyApi->addNotification($actionOwner, $viewer, $action, 'commented', array(
                    'label' => 'post'
                ));
            }

            // Add a notification for all users that commented or like except the viewer and poster
            // @todo we should probably limit this
            foreach ($action->comments()->getAllCommentsUsers() as $notifyUser) {
                if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
                    $notifyApi->addNotification($notifyUser, $viewer, $action, 'commented_commented', array(
                        'label' => 'post'
                    ));
                }
            }

            // Add a notification for all users that commented or like except the viewer and poster
            // @todo we should probably limit this
            foreach ($action->likes()->getAllLikesUsers() as $notifyUser) {
                if ($notifyUser->getIdentity() != $viewer->getIdentity() && $notifyUser->getIdentity() != $actionOwner->getIdentity()) {
                    $notifyApi->addNotification($notifyUser, $viewer, $action, 'liked_commented', array(
                        'label' => 'post'
                    ));
                }
            }

            // Stats
            Engine_Api::_()->getDbtable('statistics', 'core')->increment('core.comments');

            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        // Assign message for json
        $this->view->status = true;
        $this->view->message = 'Comment posted';

        $composerDatas = $this->getRequest()->getParam('composer', null);
        if ($action && !empty($composerDatas['tag'])) {
            $tagsArray = array();
            parse_str($composerDatas['tag'], $tagsArray);

            if (!empty($tagsArray)) {
                $commentRow->params = Zend_Json::encode(array('tags' => $tagsArray));
                $commentRow->save();
                $this->sendTagNotification($tagsArray, $action);
            }
        }

        // Redirect if not json
        if (null === $this->_getParam('format', null)) {
            $this->_redirect($form->return_url->getValue(), array('prependBase' => false));
        } elseif ('json'===$this->_getParam('format', null)) {
            $method = 'update';
            $show_all_comments = $this->_getParam('show_all_comments');
            //$showAllComments = $this->_getParam('show_all_comments', false);
            $this->view->body = $this->view->activity($action, array('noList' => true), $method, $show_all_comments);
        }
    }

    /**
     * Handles HTTP POST request to share an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/share
     *
     * @return void
     */
    public function shareAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        $type = $this->_getParam('type');
        $id = $this->_getParam('id');
        $action_id = $this->_getParam('action_id');
        if(isset($action_id)) {
            $actionItem = Engine_Api::_()->getItem('activity_action', $action_id);
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $this->view->attachment = $attachment = Engine_Api::_()->getItem($type, $id);
        $this->view->form = $form = new Activity_Form_Share();

        if (!$attachment) {
            // tell smoothbox to close
            $this->view->status  = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it has been removed.');
            $this->view->smoothboxClose = true;
            return $this->render('deletedItem');
        }


        // hide facebook and twitter option if not logged in
        $facebookTable = Engine_Api::_()->getDbtable('facebook', 'user');
        if (!$facebookTable->isConnected()) {
            $form->removeElement('post_to_facebook');
        }

        $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
        if (!$twitterTable->isConnected()) {
            $form->removeElement('post_to_twitter');
        }



        if (!$this->getRequest()->isPost()) {
            return;
        }

        if (!$form->isValid($this->getRequest()->getPost())) {
            return;
        }

        // Process

        $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
        $db->beginTransaction();

        try {
            // Get body
            $body = $form->getValue('body');
            // Set Params for Attachment
            $params = array(
                'type' => '<a href="'.$attachment->getHref().'">'.$attachment->getMediaType().'</a>',
                'privacy' => isset($action_id) ? $actionItem->privacy : (isset($attachment->networks) ? 'network_'. implode(',network_', explode(',',$attachment->networks)) : null),
            );

            // Add activity
            $api = Engine_Api::_()->getDbtable('actions', 'activity');
            //$action = $api->addActivity($viewer, $viewer, 'post_self', $body);
            $action = $api->addActivity($viewer, $attachment->getOwner(), 'share', $body, $params);
            if ($action) {
                $api->attachActivity($action, $attachment);
            }
            $db->commit();

            // Notifications
            $notifyApi = Engine_Api::_()->getDbtable('notifications', 'activity');
            // Add notification for owner of activity (if user and not viewer)
            if ($action->subject_type == 'user' && $attachment->getOwner()->getIdentity() != $viewer->getIdentity()) {
                $notifyApi->addNotification($attachment->getOwner(), $viewer, $action, 'shared', array(
                    'label' => $attachment->getMediaType(),
                ));
            }

            // Preprocess attachment parameters
            $publishMessage = html_entity_decode($form->getValue('body'));
            $publishUrl = null;
            $publishName = null;
            $publishDesc = null;
            $publishPicUrl = null;
            // Add attachment
            if ($attachment) {
                $publishUrl = $attachment->getHref();
                $publishName = $attachment->getTitle();
                $publishDesc = $attachment->getDescription();
                if (empty($publishName)) {
                    $publishName = ucwords($attachment->getShortType());
                }
                if (($tmpPicUrl = $attachment->getPhotoUrl())) {
                    $publishPicUrl = $tmpPicUrl;
                }
                // prevents OAuthException: (#100) FBCDN image is not allowed in stream
                if ($publishPicUrl &&
                    preg_match('/fbcdn.net$/i', parse_url($publishPicUrl, PHP_URL_HOST))) {
                    $publishPicUrl = null;
                }
            } else {
                $publishUrl = $action->getHref();
            }
            // Check to ensure proto/host
            if ($publishUrl &&
                false === stripos($publishUrl, 'http://') &&
                false === stripos($publishUrl, 'https://')) {
                $publishUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishUrl;
            }
            if ($publishPicUrl &&
                false === stripos($publishPicUrl, 'http://') &&
                false === stripos($publishPicUrl, 'https://')) {
                $publishPicUrl = 'http://' . $_SERVER['HTTP_HOST'] . $publishPicUrl;
            }
            // Add site title
            if ($publishName) {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title
                    . ": " . $publishName;
            } else {
                $publishName = Engine_Api::_()->getApi('settings', 'core')->core_general_site_title;
            }


            // Publish to twitter, if checked & enabled
            if ($this->_getParam('post_to_twitter', false) &&
                'publish' == Engine_Api::_()->getApi('settings', 'core')->core_twitter_enable) {
                try {
                    $twitterTable = Engine_Api::_()->getDbtable('twitter', 'user');
                    if ($twitterTable->isConnected()) {

                        // Get attachment info
                        $title = $attachment->getTitle();
                        $url = $attachment->getHref();
                        $picUrl = $attachment->getPhotoUrl();

                        // Check stuff
                        if ($url && false === stripos($url, 'http://')) {
                            $url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
                        }
                        if ($picUrl && false === stripos($picUrl, 'http://')) {
                            $picUrl = 'http://' . $_SERVER['HTTP_HOST'] . $picUrl;
                        }

                        // Try to keep full message
                        // @todo url shortener?
                        $message = html_entity_decode($form->getValue('body'));
                        if (strlen($message) + strlen($title) + strlen($url) + strlen($picUrl) + 9 <= 140) {
                            if ($title) {
                                $message .= ' - ' . $title;
                            }
                            if ($url) {
                                $message .= ' - ' . $url;
                            }
                            if ($picUrl) {
                                $message .= ' - ' . $picUrl;
                            }
                        } elseif (strlen($message) + strlen($title) + strlen($url) + 6 <= 140) {
                            if ($title) {
                                $message .= ' - ' . $title;
                            }
                            if ($url) {
                                $message .= ' - ' . $url;
                            }
                        } else {
                            if (strlen($title) > 24) {
                                $title = Engine_String::substr($title, 0, 21) . '...';
                            }
                            // Sigh truncate I guess
                            if (strlen($message) + strlen($title) + strlen($url) + 9 > 140) {
                                $message = Engine_String::substr($message, 0, 140 - (strlen($title) + strlen($url) + 9)) - 3 . '...';
                            }
                            if ($title) {
                                $message .= ' - ' . $title;
                            }
                            if ($url) {
                                $message .= ' - ' . $url;
                            }
                        }

                        $twitter = $twitterTable->getApi();
                        $twitter->statuses->update($message);
                    }
                } catch (Exception $e) {
                    // Silence
                }
            }


        } catch (Exception $e) {
            $db->rollBack();
            throw $e; // This should be caught by error handler
        }

        // If we're here, we're done
        $this->view->status = true;
        $this->view->message =  Zend_Registry::get('Zend_Translate')->_('Success!');

        // Redirect if in normal context
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $return_url = $form->getValue('return_url', false);
            if (!$return_url) {
                $return_url = $this->view->url(array(), 'default', true);
            }
            return $this->_helper->redirector->gotoUrl($return_url, array('prependBase' => false));
        } elseif ('smoothbox' === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_forward('success', 'utility', 'core', array(
                'smoothboxClose' => 10,
                'parentRefresh'=> 10,
                'messages' => array('')
            ));
        }
    }

    /**
     * Handles HTTP POST request to edit an activity feed item
     * @return void
     */
    public function editAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        $this->view->action_id = $actionId = $this->_getParam('action_id', null);
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($actionId);
        if (empty($actionId) || empty($action)) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Invalid data.');
            return;
        }
        if (!$action->canEdit()) {
            $this->view->status = false;
            $this->view->error = Zend_Registry::get('Zend_Translate')->_('Not allowed.');
            return;
        }

        $form = new Activity_Form_EditPost();
        $form->body->addFilters(array(
            new Engine_Filter_HtmlSpecialChars(),
            new Engine_Filter_EnableLinks(),
        ));
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        // Process
        $db = Engine_Db_Table::getDefaultAdapter();
        $db->beginTransaction();
        $values = $form->getValues();
        $body = $values['body'];
        $body = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $values['body'] = html_entity_decode($body, ENT_QUOTES, 'UTF-8');
        $bodyEmojis = explode(' ', $body);
        foreach($bodyEmojis as $bodyEmoji) {
            $emojisCode = Engine_Text_Emoji::encode($bodyEmoji);
            $body = $values['body'] = str_replace($bodyEmoji,$emojisCode,$body);
        }
        try {
            $privacy = 'privacy_'.$action->getIdentity();
            $privacyValue = (($values['networkprivacy']=="multi_networks") ? $_POST[$privacy] : $values['networkprivacy']);
            $values['privacy'] = $privacyValue;
            $action->setFromArray($values);
            $action->save();

            if(isset($values['privacy'])){
                // Rebuild privacy
                Engine_Api::_()->getDbTable('actions','activity')->resetActivityBindings($action);
            }
            // Try edit attachment getting stuff
            $attachmentData = $this->getRequest()->getParam('attachment');
            if (!empty($attachmentData) && !empty($attachmentData['type'])) {
                $type = $attachmentData['type'];
                $config = null;
                foreach (Zend_Registry::get('Engine_Manifest') as $data) {
                    if (empty($data['composer'][$type]['allowEdit'])) {
                        continue;
                    }
                    $config = $data['composer'][$type];
                }
                if (!empty($config['auth']) && !Engine_Api::_()->authorization()->isAllowed($config['auth'][0], null, $config['auth'][1])) {
                    $config = null;
                }
                if ($config) {
                    $attachmentData['actionBody'] = $body;
                    $plugin = Engine_Api::_()->loadClass($config['plugin']);
                    $method = 'onEditAttach'.ucfirst($type);
                    if (method_exists($plugin, $method)) {
                        $plugin->$method($action, $attachmentData);
                    }
                }
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }

        $composerDatas = $this->getRequest()->getParam('composer', null);
        if ($action && !empty($composerDatas['tag'])) {
            $tagsArray = array();
            parse_str($composerDatas['tag'], $tagsArray);

            if (!empty($tagsArray)) {
                $action->params = array_merge((array) $action->params, array('tags' => $tagsArray));
                $action->save();
                $this->sendTagNotification($tagsArray, $action);
            }
        }

        // Redirect if not json context\\
        if (null === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->_helper->redirector->gotoRoute(array(), 'default', true);
        } elseif ('json' === $this->_helper->contextSwitch->getCurrentContext()) {
            $this->view->body = $this->view->activity($action, array('noList' => true));
        }
    }

    /**
     * Handles HTTP POST request to delete a comment or an activity feed item
     *
     * Uses the default route and can be accessed from
     *  - /activity/index/delete
     *
     * @return void
     */
    public function deleteAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        $viewer = Engine_Api::_()->user()->getViewer();
        $activity_moderate = Engine_Api::_()->getDbtable('permissions', 'authorization')->getAllowed('user', $viewer->level_id, 'activity');


        // Identify if it's an action_id or comment_id being deleted
        $this->view->comment_id = $comment_id = (int) $this->_getParam('comment_id', null);
        $this->view->action_id  = $action_id  = (int) $this->_getParam('action_id', null);

        $action       = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
        if (!$action) {
            // tell smoothbox to close
            $this->view->status  = true;
            $this->view->message = Zend_Registry::get('Zend_Translate')->_('You cannot share this item because it has been removed.');
            $this->view->smoothboxClose = true;
            return $this->render('deletedItem');
        }

        // Send to view script if not POST
        if (!$this->getRequest()->isPost()) {
            return;
        }


        // Both the author and the person being written about get to delete the action_id
        if (!$comment_id && (
                $activity_moderate ||
                ('user' == $action->subject_type && $viewer->getIdentity() == $action->subject_id) || // owner of profile being commented on
                ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id))) {   // commenter
            // Delete action item and all comments/likes
            $db = Engine_Api::_()->getDbtable('actions', 'activity')->getAdapter();
            $db->beginTransaction();
            try {
                $action->deleteItem();
                $db->commit();

                // tell smoothbox to close
                $this->view->status  = true;
                $this->view->message = Zend_Registry::get('Zend_Translate')->_('This activity item has been removed.');
                $this->view->smoothboxClose = true;
                return $this->render('deletedItem');
            } catch (Exception $e) {
                $db->rollback();
                $this->view->status = false;
            }
        } elseif ($comment_id) {
            $comment = $action->comments()->getComment($comment_id);
            // allow delete if profile/entry owner
            $db = Engine_Api::_()->getDbtable('comments', 'activity')->getAdapter();
            $db->beginTransaction();
            if ($activity_moderate ||
                ('user' == $comment->poster_type && $viewer->getIdentity() == $comment->poster_id) ||
                ('user' == $action->object_type  && $viewer->getIdentity() == $action->object_id)) {
                try {
                    $action->comments()->removeComment($comment_id);
                    $db->commit();
                    $this->view->message = Zend_Registry::get('Zend_Translate')->_('Comment has been deleted');
                    return $this->render('deletedComment');
                } catch (Exception $e) {
                    $db->rollback();
                    $this->view->status = false;
                }
            } else {
                $this->view->message = Zend_Registry::get('Zend_Translate')->_('You do not have the privilege to delete this comment');
                return $this->render('deletedComment');
            }
        } else {
            // neither the item owner, nor the item subject.  Denied!
            $this->_forward('requireauth', 'error', 'core');
        }
    }

    public function getLikesAction()
    {
        $action_id = $this->_getParam('action_id');
        $comment_id = $this->_getParam('comment_id');

        if (!$action_id ||
            !$comment_id ||
            !($action = Engine_Api::_()->getItem('activity_action', $action_id)) ||
            !($comment = $action->comments()->getComment($comment_id))) {
            $this->view->status = false;
            $this->view->body = '-';
            return;
        }

        $likes = $comment->likes()->getAllLikesUsers();
        $this->view->body = $this->view->translate(array('%s likes this', '%s like this',
            count($likes)), strip_tags($this->view->fluentList($likes)));
        $this->view->status = true;
    }

    public function addMultipleNetworksAction()
    {
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        $this->view->networkLists = $networkLists = Engine_Api::_()->activity()
            ->getNetworks(
                Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0),
                Engine_Api::_()->user()->getViewer()
            );
    }

    public function editMultipleNetworksAction(){
        $this->view->action_id = $action_id = $this->_getParam('action_id', null);
        $action = Engine_Api::_()->getDbtable('actions', 'activity')->getActionById($action_id);
        $this->view->privacy = $privacy = $action['privacy'];
        if (!$this->_helper->requireUser()->isValid()) {
            return;
        }

        $this->view->networkLists = $networkLists = Engine_Api::_()->activity()
            ->getNetworks(
                Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0),
                Engine_Api::_()->user()->getViewer()
            );
    }

    private function sendTagNotification($tagsArray, $action)
    {
        $viewer = Engine_Api::_()->_()->user()->getViewer();
        $typeName = Zend_Registry::get('Zend_Translate')->translate('post');

        if (is_array($typeName)) {
            $typeName = $typeName[0];
        } else {
            $typeName = 'post';
        }
        $notificationAPi = Engine_Api::_()->getDbtable('notifications', 'activity');
        foreach ($tagsArray as $key => $tagStrValue) {
            $tag = Engine_Api::_()->getItemByGuid($key);
            if ($tag && ($tag instanceof User_Model_User) && !$tag->isSelf($viewer)) {
                $notificationAPi->addNotification($tag, $viewer, $action, 'tagged', array(
                    'object_type_name' => $typeName,
                    'label' => $typeName,
                ));
            }
        }
    }

    private function addHashtags($comment)
    {
        if(empty($comment->body)) {
            return;
        }

        $commentItem = Engine_Api::_()->getItem('activity_comment', $comment->comment_id);
        if (empty($commentItem)) {
            $commentItem = Engine_Api::_()->getItem('core_comment', $comment->comment_id);
        }

        $hashtags = Engine_Api::_()->activity()->getHashTags($comment->body);
        $commentItem->tags()->addTagMaps(Engine_Api::_()->user()->getViewer(), $hashtags[0]);
    }
}
