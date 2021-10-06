<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_SearchHashtagsController extends Engine_Content_Widget_Abstract {
  public function indexAction() {
    if (!in_array(
      'hashtags',
      Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options')
    )) {
      return $this->setNoRender();
    }

    // Make form
    $this->view->form = $form = new Core_Form_SearchHashtags();
    $params = Zend_Controller_Front::getInstance()->getRequest()->getParams();
    $form->populate($params);
  }
}
