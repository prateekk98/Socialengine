<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Viglink.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Settings_Viglink extends Engine_Form
{
  public function init()
  {
    $defaultId = md5(Engine_Api::_()->getDbtable('settings', 'core')->getSetting('core.license.key'));

    // Set form attributes
    $this->setTitle('VigLink');

    $description = $this->getTranslator()->translate(
        'Provide your <a href="%1$s" target="_blank">VigLink</a> API key.');
    $description = vsprintf($description, array(
      'http://www.viglink.com/?vgref=33113'
    ));
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      $moreInfo = $this->getTranslator()->translate(
          '<br />More Info: <a href="https://socialengine.atlassian.net/wiki/spaces/SU/pages/5112011/se-php-viglink-setup" target="_blank">KB Article</a>');
    } else {
      $moreInfo = '';
    }
    $this->setDescription($description . $moreInfo);

    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);

    // element: enabled
    $this->addElement('Radio', 'enabled', array(
      'label' => 'Enable?',
      'multiOptions' => array(
        '1' => 'Yes, enable VigLink on my site.',
        '0' => 'No, VigLink is disabled.',
      ),
      'value' => 0,
    ));

    // Element: code
    $this->addElement('Text', 'code', array(
      'label' => 'API Key',
      'filters' => array(
        'StringTrim',
      )
    ));

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }
}
