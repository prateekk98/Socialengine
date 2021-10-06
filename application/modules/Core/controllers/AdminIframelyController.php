<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminMailController.php 9798 2012-10-12 19:11:49Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_AdminIframelyController extends Core_Controller_Action_Admin
{
  public function indexAction()
  {
    $this->view->form = $form = new Core_Form_Admin_Settings_Iframely();
    $settings = Engine_Api::_()->getApi('settings', 'core');
    // Populate form
    $form->populate($settings->getFlatSetting('core_iframely', array('host' => 'none')));

    if (_ENGINE_ADMIN_NEUTER) {
      $form->secretIframelyKey->setValue("**********");
    }
    
    // Check post/valid
    if( !$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost()) ) {
      return;
    }

    // Process form
    $values = $form->getValues();
    if( $values['host'] != 'none' ) {
      try {
        $resonse = Engine_Iframely::factory(array_merge($values, array('ignoreError' => false)))
          ->test();
        if( !empty($resonse['error']) ) {
          $form->addError($resonse['error']);
          return;
        }
      } catch( Exception $e ) {
        $form->addError($e->getMessage());
        return;
      }
    }
    // Save settings
    $settings->core_iframely = $values;

    $form->addNotice('Your changes have been saved.');
  }

}
