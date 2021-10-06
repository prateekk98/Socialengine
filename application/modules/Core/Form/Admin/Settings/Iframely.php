<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Iframely.php 9747 2012-07-26 02:08:08Z john $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Settings_Iframely extends Engine_Form
{
  public function init()
  {
    $kbText = '';
    $settings = Engine_Api::_()->getApi('settings', 'core');
    if( $settings->getSetting('user.support.links', 0) == 1 ) {
      $kbText = '<br> More Info: %2$s';
    }

    $description = sprintf(
      'Integrate the Iframely API with your SocialEngine website. This integration works with the \'Add Link\' feature for the Activity Feed and enhances links shared by the community.'
      .' %1$s to learn about all the awesome features Iframely provides.' . $kbText,
      '<a href="https://iframely.com/features" target="_blank">Click here</a>',
      '<a href="https://socialengine.atlassian.net/wiki/spaces/SU/pages/5210486/se-php-iframely" target="_blank">KB Article</a>'
    );

    // Set form attributes
    $this->setTitle('Iframely Integration');
    $this->setDescription($description);
    // Decorators
    $this->loadDefaultDecorators();
    $this->getDecorator('Description')->setOption('escape', false);
    // Element: enabled
    $multiOptions = array();
    foreach( Engine_Iframely::getHostingList() as $host ) {
      $multiOptions[$host] = $this->getTranslator()->translate('CORE_FORM_ADMIN_SETTINGS_IFRAMELY_OPTION_' . strtoupper($host));
    }
    $this->addElement('Radio', 'host', array(
      'label' => 'Integration Mode',
      'multiOptions' => $multiOptions,
      'attribs' => array(
        'escape' => false,
      ),
      'onclick' => 'updateFields();',
    ));


    $this->addElement('Text', 'baseUrl', array(
      'label' => 'Iframely Base Url',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));

    $this->addElement('Text', 'secretIframelyKey', array(
      'label' => 'API Key',
      'filters' => array(
        new Zend_Filter_StringTrim(),
      ),
    ));

    // init submit
    $this->addElement('Button', 'submit', array(
      'label' => 'Save Changes',
      'type' => 'submit',
      'ignore' => true,
    ));
  }

}
