<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Controller.php 2015-01-22 00:00:53Z shaun $
 * @author     John Boehr <john@socialengine.com>
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_Widget_BrowseSearchController extends Engine_Content_Widget_Abstract
{
    public function indexAction()
    {
        // Prepare form
        $this->view->form = $form = new User_Form_Search(array(
            'type' => 'user'
        ));

        $p = Zend_Controller_Front::getInstance()->getRequest()->getParams();
        $form->populate($p);
        $this->view->topLevelId = $form->getTopLevelId();
        $this->view->topLevelValue = $form->getTopLevelValue();
    }
}
