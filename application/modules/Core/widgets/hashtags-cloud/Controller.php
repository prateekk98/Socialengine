<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_HashtagsCloudController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    if (!in_array('hashtags', Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'))) {
      return $this->setNoRender();
    }

    $limit = $this->_getParam('tag_count', 10);
    $hashtagMapTable = Engine_Api::_()->getDbtable('tags', 'core');
    $hashtagNames = $hashtagMapTable->getTopHashtags($limit);
    if (empty($hashtagNames)) {
      return $this->setNoRender();
    }

    $this->view->hashtags = $hashtagNames;
  }
}
