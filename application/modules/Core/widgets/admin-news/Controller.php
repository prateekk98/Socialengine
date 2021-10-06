<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 9747 2012-07-26 02:08:08Z john $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Widget_AdminNewsController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Zend_Feed required DOMDocument
        if (!class_exists('DOMDocument', false)) {
            $this->view->badPhpVersion = true;
            return;
        }
    }
}
