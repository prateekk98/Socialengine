<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Post.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Form_EditPost extends Engine_Form
{
  public function init()
  {

    $this
      ->setMethod('POST')
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(array(
          'module' => 'activity', 'controller' => 'index', 'action' => 'edit'), 'default', true))
      ->setAttrib('class', 'global_form_activity_edit_post')
    ;

    $this->addElement('Textarea', 'body', array(
      'attribs' => array('rows' => 3),
//      'filters' => array(
//        new Engine_Filter_Censor(),
//        new Engine_Filter_Html(array('AllowedTags' => 'br')),
//      ),
    ));

    $privacy = array();
    $defaultViewPrivacy = array(
      'everyone'  => 'Everyone',
      'networks'  => 'Friends & Networks',
      'friends'   => 'Friends Only',
      'onlyme'    => 'Only Me',
    );
    $viewPrivacyLists = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.view.privacy');
    if (!empty($viewPrivacyLists)) {
      foreach ($viewPrivacyLists as $viewPrivacy) {
        $privacyArray[$viewPrivacy] = $defaultViewPrivacy[$viewPrivacy];
      }
    }

    $enableNetworkList = Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0);
    if ($enableNetworkList) {
      $networkLists = Engine_Api::_()->activity()->getNetworks($enableNetworkList, Engine_Api::_()->user()->getViewer());

      if ((is_array($networkLists) || is_object($networkLists)) && count($networkLists)) {
        foreach ($networkLists as $network) {
          $networkArray["network_" . $network->getIdentity()] = $network->getTitle();
        }
      }
    }
    $translate = Zend_Registry::get('Zend_Translate');

    $privacy = array_merge(
      isset($privacyArray) ? $privacyArray : array(),
      isset($networkArray) ? $networkArray : array(),
      isset($networkArray) ? array("multi_networks" => $translate->translate("Multiple Networks")) : array()
    );

    $this->addElement('Select', 'networkprivacy', array(
      'label' => 'Privacy',
      'multiOptions' => $privacy,
      'onclick' => "setEditPrivacyValue(this.value,action_id.value);"
    ));

    $this->addElement('hidden', 'action_id');

    // Buttons
    $this->addElement('Button', 'submit', array(
      'label' => 'Edit Post',
      'type' => 'submit',
      'ignore' => true,
      'decorators' => array('ViewHelper')
    ));

    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'prependText' => ' or ',
      'class' => 'feed-edit-content-cancel',
      'href' => 'javascript:void(0);',
      'decorators' => array(
        'ViewHelper'
      )
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
