<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     Jung
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */

class Core_Widget_LandingPageBannerController extends Engine_Content_Widget_Abstract {

  public function indexAction() {
    // Get banner
    $this->view->height = $this->_getParam('height', '400');
    $bannerId = $this->_getParam('bannerId', 0);
    $this->view->banner = $banner = Engine_Api::_()->getDbtable('banners', 'core')->getBanner($bannerId);
    if( !$banner ) {
      return $this->setNoRender();
    }
  }
}
