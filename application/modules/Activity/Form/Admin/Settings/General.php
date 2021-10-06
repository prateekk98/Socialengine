<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: General.php 10249 2014-05-30 22:38:38Z andres $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Activity
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Activity_Form_Admin_Settings_General extends Engine_Form
{
  public function init()
  {

    $this->addElement('Text', 'length', array(
      'label' => 'Overall Feed Length',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_LENGTH_DESCRIPTION',
      'value' => 15,
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('Int', true),
        array('Between', true, array(1, 50, true)),
        //array('GreaterThan', true, array(0)),
      ),
    ));
    
    $this->addElement('Text', 'userlength', array(
      'label' => 'Item Limit Per User',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_USERLENGTH_DESCRIPTION',
      'value' => 5,
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('Int', true),
        array('Between', true, array(1, 50, true)),
        //array('GreaterThan', true, array(0)),
      ),
    ));

    $this->addElement('Text', 'postLength', array(
      'label' => 'Post Feed Character Limit',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_POSTLENGTH_DESCRIPTION',
      'value' => 1000,
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        array('Int', true),
        array('GreaterThan', true, array(-1)),
      ),
    ));

    $this->addElement('Select', 'liveupdate', array(
      'label' => 'Update Frequency',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_DESCRIPTION',
      'value' => 120000,
      'multiOptions' => array(
        30000  => 'ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION1',
        60000  => 'ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION2',
        120000 => "ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION3",
        0      => 'ACTIVITY_FORUM_ADMIN_SETTINGS_GENERAL_LIVEUPDATE_OPTION4'
      )
    ));

    $this->addElement('Radio', 'userdelete', array(
      'label' => 'Item Deletion',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_USERDELETE_DESCRIPTION',
      'value' => 1,
      'multiOptions' => array(
        1 => 'Yes, allow members to delete their feed items.',
        0 => 'No, members may not delete their feed items.'
      )
    ));

    $this->addElement('Radio', 'content', array(
      'label' => 'Feed Content',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_CONTENT_DESCRIPTION',
      'value' => 'everyone',
      'multiOptions' => array(
        'everyone' => 'All Members',
        'networks' => 'My Friends & Networks',
        'friends' => 'My Friends'
      )
    ));

    /*
    $this->addElement('Radio', 'filter', array(
      'label' => 'Feed Item Filtering',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_FILTER_DESCRIPTION',
      'value' => 1,
      'multiOptions' => array(
        1 => 'Yes, members can choose not to see certain feed item types.',
        0 => 'No, members cannot customize their view of the feed.'
      )
    ));
    */

    $this->addElement('Radio', 'publish', array(
      'label' => 'Item Publishing Option',
      'description' => 'ACTIVITY_FORM_ADMIN_SETTINGS_GENERAL_PUBLISH_DESCRIPTION',
      'value' => 1,
      'multiOptions' => array(
        1 => 'Yes, members may specify which of their item types will not be published.',
        0 => 'No, members may not specify which of their item types will not be published.'
      )
    ));

    $href = Zend_Registry::get('Zend_View')->url(array('action' => 'manage-emoticons'));
    $description = sprintf(
      "%1sClick here%2s to add custom emoticons. ",
      "<a href='$href' target='_blank'>", "</a>"
    );
    $this->addElement('MultiCheckbox', 'composer_options', array(
      'description' => 'Select options to be enabled in Status Post Box for activity feeds.',
      'label' => 'Status Post Box Options',
      'escape' => false,
      'multiOptions' => array(
        'emoticons' => 'Emoticons / Smileys ( Enabling this will add an "Insert Emoticons" icon in the status post box and will allow users to insert attractive Emoticons / Smileys in their status updates. Symbols for smileys entered in status updates as well as comments of activity feeds will also be displayed as respective emoticons. ' . $description . ')',
        'userTags' => 'User Mentions ( Users will be able to mention / tag their friends in the status updates and comments. Tagged / Mentioned friends will receive a notification for this.)',
        'hashtags' => 'Hashtags ( Enabling this will allow users to post hashtags in the status post box. Users can also be able to view top trending hashtags. )'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.composer.options'),
    ));

    $this->addElement('MultiCheckbox', 'view_privacy', array(
      'label' => 'Posts Privacy Options',
      'description' => 'Your users can choose from any of the options checked below when they decide who can see their posts',
      'multiOptions' => array(
        'everyone'  => 'Everyone',
        'networks'  => 'Friends & Networks',
        'friends'   => 'Friends Only',
        'onlyme'    => 'Just Me',
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting(
        'activity.view.privacy',
        array('everyone', 'registered', 'network', 'member', 'owner')
      ),
    ));

    $this->addElement('Radio', 'network_privacy', array(
      'label' => 'Allow Network Selection as a post privacy option ?',
      'multiOptions' => array(
        2 => 'Yes, show all the available networks.',
        1 => 'Yes, show only those networks that have been joined by the user.',
        0 => 'No'
      ),
      'value' => Engine_Api::_()->getApi('settings', 'core')->getSetting('activity.network.privacy', 0),
    ));

    $this->addElement('Radio', 'commentreverseorder', array(
      'label' => 'Comment Sorting Order',
      'value' => 0,
      'multiOptions' => array(
        0 => 'Chronological',
        1 => 'Reverse chronological'
      )
    ));

    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true
    ));
  }
}
