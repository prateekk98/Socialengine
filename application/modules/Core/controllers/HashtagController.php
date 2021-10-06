<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: HashtagController.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_HashtagController extends Core_Controller_Action_Standard
{
    public function indexAction() {
        if (!in_array(
            'hashtags',
            Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options')
        )) {
            return $this->_forward('notfound', 'error', 'core');
        }

        $this->_helper->content
            ->setEnabled()
        ;
    }
}
