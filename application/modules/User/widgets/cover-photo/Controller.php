<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Widget_CoverPhotoController extends Engine_Content_Widget_Abstract {

  public function indexAction() {

    $viewer = Engine_Api::_()->user()->getViewer();
    $this->view->viewer_id = $viewer_id = $viewer->getIdentity();

    if (!Engine_Api::_()->core()->hasSubject('user')) {
      $this->view->user = $user = $viewer;
    } else {
      $this->view->user = $user = Engine_Api::_()->core()->getSubject('user');
    }

    if (!$user->getIdentity()) {
      return $this->setNoRender();
    }

    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();

    $this->view->photo = '';
    if (isset($user->coverphoto)) {
      $this->view->photo = $photo = Engine_Api::_()->getItem('storage_file', $user->coverphoto);
    }
    $this->view->level_id = 0;
    $this->view->uploadDefaultCover = 0;

    if ($viewer->getIdentity() && $viewer->level_id == 1 && $user->getOwner()->isSelf($viewer)) {
      $this->view->uploadDefaultCover = isset($params['uploadDefaultCover']) ? $params['uploadDefaultCover'] : 0;
      $this->view->level_id = isset($params['level_id']) ? $params['level_id'] : 0;
    }

    if ($this->view->uploadDefaultCover) {
      $levelsAssoc = Engine_Api::_()->getDbtable('levels', 'authorization')->getLevelsAssoc();
      $this->view->defaultCoverMessage = Zend_Registry::get('Zend_Translate')->_(sprintf(
        'Here, you can upload and set default user cover photo for ' . $levelsAssoc[$this->view->level_id] .
          '. %s to view your profile.',
        "<a href='". $viewer->getHref() . "'>Click here</a>"
      ));
    }

    $this->view->can_edit = $user->authorization()->isAllowed($viewer, 'edit') && Engine_Api::_()->authorization()
      ->isAllowed('user', $user, 'coverphotoupload');
  }
}
