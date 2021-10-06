<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    UserUsers.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_UserFieldsData extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_priority = 4999;
  protected $_customFields = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'user_custom';
    $this->_toTable = 'engine4_user_fields_values';
  }

  protected function _runPre()
  {
    $this->_customFields = $this->getAllCustomFieldMap();
  }

  protected function _translateRow(array $data, $key = null)
  {

    if( is_null($this->_customFields) || !is_array($this->_customFields) )
      return false;

    foreach( $this->_customFields as $customField ) {
      $indx = array();
      $optionsModel = $this->getFromDb()
        ->select()
        ->from($this->getfromPrefix() . 'user_custom_multiple_value', 'option_id')
        ->where('user_id = ?', $data['user_id'])
        ->where('field_id = ?', $customField['fromFieldId'])
        ->query()
        ->fetchAll();
      $optionValue = '';
      //Insertion of option value of custom field
      if( $optionsModel !== false && count($optionsModel) > 0 && !is_null($optionsModel) ) {
        // if a field having multiple values then multiple values will be inserted
        foreach( $optionsModel as $optionModel ) {
          $optionId = $optionModel['option_id'];
          $optionValue = '';
          if( !empty($optionId) ) {
            $optionValue = $this->getFromDb()
              ->select()
              ->from($this->getfromPrefix() . 'custom_option', 'phrase_var_name')
              ->where('option_id = ?', $optionId)
              ->limit(1)
              ->query()
              ->fetchColumn(0);
          }
          if( is_null($optionValue) || empty($optionValue) ) {
            $fieldValue = $data[$customField['fieldName']];
          } else {

            $option = $this->getLabelByPharseVar($optionValue);
            $fieldValue = $this->getToDb()
              ->select()
              ->from('engine4_user_fields_options', 'option_id')
              ->where('field_id = ?', $customField['toFieldId'])
              ->where('label = ?', $option)
              ->query()
              ->fetchColumn(0);
          }
          if( is_null($fieldValue) || empty($fieldValue) )
            $fieldValue = "";

          $fieldValue = mb_convert_encoding($fieldValue, "UTF-8", "HTML-ENTITIES");
          $indexVal = 0;
          if( isset($indx[$customField['toFieldId']]) )
            $indexVal = $indx[$customField['toFieldId']] = $indx[$customField['toFieldId']] + 1;
          else
            $indexVal = $indx[$customField['toFieldId']] = 0;

          $fieldValueExist = $this->getToDb()
            ->select()
            ->from('engine4_user_fields_values', 'item_id')
            ->where('item_id = ?', $data['user_id'])
            ->where('field_id = ?', $customField['toFieldId'])
            ->where('`index` = ?', $indexVal)
            ->query()
            ->fetchColumn(0);
          // Insert the option value
          if( empty($fieldValueExist) ) {
            $this->getToDb()
              ->insert('engine4_user_fields_values', array
                (
                'item_id' => $data['user_id'],
                'field_id' => $customField['toFieldId'],
                'index' => $indexVal,
                'value' => $fieldValue,
                'privacy' => 'everyone'
                )
            );
          }
        }
      } else {
        //Insertion of non optional field data(like text field , textarea data ).
        $fieldValue = mb_convert_encoding($data[$customField['fieldName']], "UTF-8", "HTML-ENTITIES");
        if( is_null($fieldValue) || empty($fieldValue) )
          $fieldValue = "";

        $fieldValue = $this->getToDb()
          ->select()
          ->from('engine4_user_fields_values', 'item_id')
          ->where('item_id = ?', $data['user_id'])
          ->where('field_id = ?', $customField['toFieldId'])
          ->where('`index` = ?', 0)
          ->query()
          ->fetchColumn(0);
        if( empty($fieldValue) ) {
          $this->getToDb()
            ->insert('engine4_user_fields_values', array
              (
              'item_id' => $data['user_id'],
              'field_id' => $customField['toFieldId'],
              'index' => 0,
              'value' => $fieldValue,
              'privacy' => 'everyone'
              )
          );
        }
      }
    }
  }
}
