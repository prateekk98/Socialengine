<?php
/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Upgrade.php 9747 2017-02-14 02:08:08Z john $
 * @author     John Boehr <j@webligo.com>
 */

/**
 * @category   Application_Extensions
 * @package    Album
 * @copyright  Copyright 2006-2017 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Core_Form_Admin_Themes_UpgradeColorVariant extends Engine_Form
{
  public function init()
  {
    $this
      ->setTitle('Upgrade Theme')
      ->setDescription('Are you sure you want to upgrade this theme? '
        . 'Upgrading will overwite all the changes in the theme, if done previously.'
        . ' So you might want to back up your themes before proceeding further.')
      ->setMethod('POST')
      ->setAttrib('class', 'global_form_popup')
      ;
    $this->addElement('hidden', 'colorVariantName', array(
      'value' => $this->getAttrib('colorVariantName'),
      'order' => 990,
    ));

    $this->addElement('hidden', 'formatType', array(
      'value' => 'smoothbox',
      'order' => 991,
    ));

    $this->addElement('Button', 'execute', array(
      'label' => 'Upgrade Theme',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
      'type' => 'submit'
    ));

    $this->addElement('Button', 'execute', array(
      'label' => 'Upgrade Theme',
      'ignore' => true,
      'decorators' => array('ViewHelper'),
      'type' => 'submit'
    ));

    $this->addElement('Cancel', 'cancel', array(
      'prependText' => ' or ',
      'label' => 'cancel',
      'link' => true,
      'href' => '',
      'onclick' => 'parent.Smoothbox.close();',
      'decorators' => array(
        'ViewHelper'
      ),
    ));

    $this->addDisplayGroup(array(
      'execute',
      'cancel'
    ), 'buttons');
  }
}
