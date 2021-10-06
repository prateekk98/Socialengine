<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AdminSettingsController.php 9949 2013-02-22 23:48:12Z jung $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    User
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class User_AdminSettingsController extends Core_Controller_Action_Admin
{
    public function indexAction()
    {
        return $this->_helper->redirector->gotoRoute(array(
            'route' => 'admin_default',
            'module' => 'authorization',
            'controller' => 'level',
            'action' => 'edit'
        ));
    }

    public function generalAction()
    {

    }

    public function emailsAction()
    {
        // Build the different email types
        $modules = Engine_Api::_()->getDbtable('modules', 'core')->getModulesAssoc();

        $emailTypes = $emailTypes = Engine_Api::_()->getDbTable('mailTemplates', 'core')->getEmailTypes();
        $emailSettings = $emailSettings = Engine_Api::_()->getDbtable('mailTemplates', 'core')->getDefaultEmails();

        $emailTypesAssoc = array();
        $emailSettingsAssoc = array();
        foreach( $emailTypes as $type ) {
            if( in_array($type->module, array('core', 'activity', 'fields', 'authorization', 'messages', 'user')) ) {
                $elementName = 'general';
                $category = 'General';
            } else if( isset($modules[$type->module]) ) {
                $elementName = preg_replace('/[^a-zA-Z0-9]+/', '-', $type->module);
                $category = $modules[$type->module]->title;
            } else {
                $elementName = 'misc';
                $category = 'Misc';
            }

            $emailTypesAssoc[$elementName]['category'] = $category;
            $emailTypesAssoc[$elementName]['types'][$type->type] = '_EMAIL_' . strtoupper($type->type) . '_TITLE';
            if( in_array($type->type, $emailSettings) ) {
                $emailSettingsAssoc[$elementName][] = $type->type;
            }
        }

        ksort($emailTypesAssoc);

        $emailTypesAssoc = array_filter(array_merge(array(
            'general' => array(),
            'misc' => array(),
        ), $emailTypesAssoc));


        $this->view->form = $form = new Engine_Form(array(
            'title' => 'Default Email Alerts',
            'description' => 'This page allows you to specify the default email alerts for new users.',
        ));

        foreach( $emailTypesAssoc as $elementName => $info ) {
            $form->addElement('MultiCheckbox', $elementName, array(
                'label' => $info['category'],
                'multiOptions' => $info['types'],
                'value' => (array) @$emailSettingsAssoc[$elementName],
            ));
        }

        // init submit
        $form->addElement('Button', 'submit', array(
            'label' => 'Save Changes',
            'type' => 'submit',
            'ignore' => true,
        ));

        // Check method
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        $values = array();
        foreach( $form->getValues() as $key => $value ) {
            if( !is_array($value) ) continue;

            foreach( $value as $skey => $svalue ) {
                if( !isset($emailTypesAssoc[$key]['types'][$svalue]) ) {
                    continue;
                }
                $values[] = $svalue;
            }
        }

        Engine_Api::_()->getDbtable('mailTemplates', 'core')->setDefaultEmails($values);
        $form->addNotice('Your changes have been saved.');
    }

    public function friendsAction()
    {
        $form = new User_Form_Admin_Settings_Friends();
        $form->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array()));
        $form->setMethod("POST");
        $this->view->form = $form;

        if( $this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) ) {
            $form->saveValues();
            $form->addNotice('Your changes have been saved.');
        }
    }

    public function facebookAction()
    {
        $form = $this->view->form = new User_Form_Admin_Facebook();
        $form->populate((array) Engine_Api::_()->getApi('settings', 'core')->core_facebook);

        if( _ENGINE_ADMIN_NEUTER ) {
            $form->populate(array(
                'appid' => '******',
                'secret' => '******',
            ));
        }
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        $values = $form->getValues();
        if( empty($values['appid']) || empty($values['secret']) ) {
            $values['appid'] = '';
            $values['secret'] = '';
            $values['enable'] = 'none';
        }

        Engine_Api::_()->getApi('settings', 'core')->core_facebook = $values;
        $form->addNotice('Your changes have been saved.');
        $form->populate($values);
    }

    public function twitterAction()
    {
        // Get form
        $form = $this->view->form = new User_Form_Admin_Twitter();
        $form->populate((array) Engine_Api::_()->getApi('settings', 'core')->core_twitter);
        if( _ENGINE_ADMIN_NEUTER ) {
            $form->populate(array(
                'key' => '******',
                'secret' => '******',
            ));
        }
        // Get classes
        include_once 'Services/Twitter.php';
        include_once 'HTTP/OAuth/Consumer.php';

        if( !class_exists('Services_Twitter', false) ||
            !class_exists('HTTP_OAuth_Consumer', false) ) {
            return $form->addError('Unable to load twitter API classes');
        }

        // Check data
        if( !$this->getRequest()->isPost() ) {
            return;
        }

        if( !$form->isValid($this->getRequest()->getPost()) ) {
            return;
        }

        $values = $form->getValues();

        if( empty($values['key']) || empty($values['secret']) ) {
            $values['key'] = '';
            $values['secret'] = '';
            $values['enable'] = 'none';
        } else {

            // Try to check credentials
            try {
                $twitter = new Services_Twitter();
                $oauth = new HTTP_OAuth_Consumer($values['key'], $values['secret']);
                //$twitter->setOAuth($oauth);
                $oauth->getRequestToken('https://twitter.com/oauth/request_token');
                $oauth->getAuthorizeUrl('http://twitter.com/oauth/authorize');

            } catch( Exception $e ) {
                return $form->addError($e->getMessage());
            }
        }

        // Okay
        Engine_Api::_()->getApi('settings', 'core')->core_twitter = $form->getValues();
        $form->addNotice('Your changes have been saved.');
        $form->populate($values);
    }

    public function levelAction()
    {
        return $this->_helper->redirector->gotoRoute(array(
            'module' => 'authorization',
            'controller' => 'level',
            'action' => 'edit'
        ));
    }
}
