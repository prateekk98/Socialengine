<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: SearchHashtags.php
 * @author     Jung
 */

/**
 * @category   Application_Extensions
 * @package    Core
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_SearchHashtags extends Engine_Form
{
  public function init()
  {
    $this
      ->setAttribs(array(
        'id' => 'filter_form',
        'class' => 'global_form_box',
      ))
      ->setAction(Zend_Controller_Front::getInstance()->getRouter()->assemble(
        array('action' => 'index'),
        "core_hashtags",
        true
      ))
      ->setMethod('GET')
      ;

    $this->addElement('Text', 'search', array(
      'label' => 'Search Hashtags',
    ));

    $this->addElement('Button', 'submit', array(
      'type' => 'submit',
      'label' => 'Search',
    ));
  }
}
