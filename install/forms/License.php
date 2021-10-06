<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: License.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Install
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Install_Form_License extends Engine_Form
{
  public function init()
  {
    // Email
    $this->addElement('Text', 'email', array(
      'label' => 'License Email:',
      'required' => true,
      'allowEmpty' => false,
      'validators' => array(
        'EmailAddress',
      )
    ));

    // Statistics
    $this->addElement('Radio', 'statistics', array(
      'label' => 'Allow us to collect information about your server environment?',
      'required' => true,
      'description' => 'With your permission, we would like to collect some '.
        'information about your server to help us improve SocialEngine in the '.
        'future. The exact information we will collect is: PHP version and ' .
        'list of extensions, MySQL version, Web-server type and version, '.
        'SocialEngine version. This information will NOT be shared with any '.
        'third party and will only be used by our development team as we build '.
        'new modules. If you do not wish to send this information, please '.
        'uncheck the box below. We sincerely appreciate your support!',
      'multiOptions' => array(
        '1' => 'Yes, allow information to be reported.',
        '0' => 'No, do not allow information to be reported.',
      ),
      'value' => '1',
    ));

    // Submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Continue...',
      'type' => 'submit',
      'order' => 10000,
      'ignore' => true,
      'decorators' => array(
        'ViewHelper',
        array('HtmlTag', array('tag' => 'div', 'class' => 'form-wrapper submit-wrapper')),
      )
    ));

    $this->addElement('Hidden', 'valid', array(
      'label' => 'Continue...',
      'type' => 'submit',
      'order' => 10001,
    ));

    // Modify decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('FormErrors')->setSkipLabels(true);
  }
}
