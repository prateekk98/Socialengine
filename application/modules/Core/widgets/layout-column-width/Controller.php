<?php

/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2016-10-21 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_LayoutColumnWidthController extends Engine_Content_Widget_Abstract
{

  public function indexAction()
  {
    $this->view->columnWidth = $this->_getParam('columnWidth', 0);
    if( empty($this->view->columnWidth) ) {
      return $this->setNoRender();
    }
    $this->view->columnWidth .= $this->_getParam('columnWidthType', 'px');
  }

}
