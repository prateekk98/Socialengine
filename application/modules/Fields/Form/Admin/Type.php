<?php
/**
 * SocialEngine
 *
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Type.php 9772 2012-08-30 22:25:06Z matthew $
 * @author     John
 */

/**
 * @category   Application_Core
 * @package    Fields
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @author     John
 */
class Fields_Form_Admin_Type extends Engine_Form
{
  public function init()
  {

    $metaTable = Engine_Api::_()->fields()->getTable('user', 'meta');
    $select = $metaTable->select()->where('type = ?', 'profile_type');
    $profileType = $metaTable->fetchRow($select);

    // Get list of Member Types
    $db = Engine_Db_Table::getDefaultAdapter();
    $memberTypeResult = $db->select('option_id, label')
            ->from('engine4_user_fields_options')
            ->where('field_id = ?', $profileType->field_id)
            ->query()
            ->fetchAll();
    $memberTypeCount = count($memberTypeResult);
    $memberTypeArray = array( 'null' => 'No, Create Blank Profile Type' );
    for( $i = 0; $i < $memberTypeCount; $i++ ) {
      $memberTypeArray[$memberTypeResult[$i]['option_id']] = $memberTypeResult[$i]['label'];
    }

    $this->setMethod('POST')
      ->setAttrib('class', 'global_form_smoothbox');

    // Add label
    $this->addElement('Text', 'label', array(
      'label' => 'Profile Type Label',
      'required' => true,
      'allowEmpty' => false,
    ));

    // Duplicate Existing
    $this->addElement('Select', 'duplicate', array(
      'label' => 'Duplicate Existing Profile Type?',
      'required' => true,
      'allowEmpty' => false,
      'multiOptions' => $memberTypeArray,
    ));


    // Add submit
    $this->addElement('Button', 'save', array(
      'label' => 'Add Profile Type',
      'type' => 'submit',
      'onClick' => 'disableSubmit(this);',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    // Add cancel
    $this->addElement('Cancel', 'cancel', array(
      'label' => 'cancel',
      'link' => true,
      'onclick' => 'parent.Smoothbox.close();',
      'prependText' => ' or ',
      'decorators' => array(
        'ViewHelper',
      ),
    ));

    $this->addDisplayGroup(array('submit', 'cancel'), 'buttons');
  }
}
