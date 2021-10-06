<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: AbstractFields.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
abstract class Install_Import_Phpfox_AbstractFields extends Install_Import_Phpfox_Abstract
{

  protected $_profileFieldsMap = 0;
  protected $_profileFieldCount = 0;
  protected $_profileOptionCount = 0;
  protected $_profileSearchCount = 0;

  public function __sleep()
  {
    return array_merge(parent::__sleep(), array(
      '_profileFieldsMap',
      '_profileFieldCount', '_profileOptionCount', '_profileSearchCount',
    ));
  }

  protected function _runPre()
  {

    $this->getToDb()->query('TRUNCATE TABLE' . $this->getToDb()->quoteIdentifier('engine4_user_fields_search'));
    $this->getToDb()->query('TRUNCATE TABLE' . $this->getToDb()->quoteIdentifier('engine4_user_fields_values'));
    $this->getToDb()->query('DELETE FROM ' . $this->getToDb()->quoteIdentifier('engine4_user_fields_options') . ' WHERE  field_id>13');
    $this->getToDb()->query('DELETE FROM ' . $this->getToDb()->quoteIdentifier('engine4_user_fields_meta') . ' WHERE  field_id>13');
    $this->getToDb()->query('DELETE FROM ' . $this->getToDb()->quoteIdentifier('engine4_user_fields_maps') . ' WHERE  child_id>13');

    $fromDb = $this->getFromDb();
    $toDb = $this->getToDb();
    $this->inializeCustomFieldMap();
    //insert the User profile custom field
    $this->_insertCustomField();
    $this->_message(sprintf('Success - %d profile fields records imported', $this->_profileFieldCount));
    $this->_message(sprintf('Success - %d profile options records imported', $this->_profileOptionCount));
    $this->_message(sprintf('Success - %d profile search records imported', $this->_profileSearchCount));
    $this->_message(sprintf('Success - %d profile field maps records imported', $this->_profileFieldsMap));
  }

  /*
   * THIS FUNCTION USED TO INSERT CLASSIFIED CUSTOM FIELD DATA SUCH AS (CURRENCY,COUNTRY,CITY,ZIP CODE,SHORT DESC)
   */
  protected function _insertOtherClassifiedCustomData()
  {

    $metaTable = 'engine4_classified_fields_meta';
    $fieldValuesTable = 'engine4_classified_fields_values';
    //FIND THE CLASSIFIED CUSTOM FIELDS DATA
    $classifiedInfo = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'marketplace', array('listing_id as item_id', 'price as currency', 'country_iso as country', 'city as city', 'postal_code as zip_code', 'mini_description as short_desc', 'trim(lower(currency_id)) as currency_unit'))
      ->query()
      ->fetchAll();
    $priceUnit = trim(strtolower($this->getParam('unit')));
    if( empty($priceUnit) )
      $priceUnit = "usd";
    //TRAVERSE EACH FIELD AND INSERT THE CLASSIFIED FIELD AND CLASSIFIED FIELD VALUES
    foreach( $classifiedInfo as $data ) {
      foreach( $data as $key => $value ) {

        if( $key == 'currency_unit' )
          continue;
        //IF CLASSIFIED FIELD HAVING CURRENCY VALUE OTHER THAN SELECTED CURRENCY THEN SET VALUE 0 FOR THAT CLASSIFIED FIELD.
        if( $key == 'currency' ) {
          if( $priceUnit != $data['currency_unit'] )
            $value = 0.00;
        }
        if( $key != 'short_desc' ) {
          // FIND FIELD
          $toFieldId = $this->getToDb()->select()
            ->from($metaTable, 'field_id')
            ->where('type = ?', $key)
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        } else {
          //FIND THE SHORT DESC FIELD
          $toFieldId = $this->getToDb()->select()
            ->from($metaTable, 'field_id')
            ->where('type = ?', 'text')
            ->where('lower(trim(label)) = ?', 'short description')
            ->limit(1)
            ->query()
            ->fetchColumn(0);
        }

        if( $toFieldId ) {

          $value = is_null($value) ? '' : $value;
          //CHECKING FOR FIELD VALUE ALREADY EXIST
          $fromCustomFieldDataExists = $this->getToDb()->select()
            ->from($fieldValuesTable, 'field_id')
            ->where('item_id = ?', $data['item_id'])
            ->where('field_id = ?', $toFieldId)
            ->limit(1)
            ->query()
            ->fetchColumn(0);
          //IF NOT THEN INSERT CLASSIFIED FIELD VALUE
          if( $fromCustomFieldDataExists === false ) {
            $this->getToDb()
              ->insert($fieldValuesTable, array
                (
                'item_id' => $data['item_id'],
                'field_id' => $toFieldId,
                'index' => 0,
                'value' => $value,
                )
            );
          }
        }
      }
    }
  }

  protected function _insertClassifiedCustomField()
  {
    if( !$this->_tableExists($this->getFromDb(), $this->getFromPrefix() . 'input_field') ) {
      $this->_log('Table: ' . $this->getFromPrefix() . 'input_field does not exist.', Zend_Log::WARN);
      return false;
    }

    $metaTable = 'engine4_classified_fields_meta';
    $mapsTable = 'engine4_classified_fields_maps';
    $fieldId = 0;
    $optionId = 0;
    $tableInfo = array('fieldTable' => $metaTable, 'mapTable' => $mapsTable, 'fieldId' => $fieldId, 'optionId' => $optionId);

    // Inserting short description custom field

    $fieldInfo = array('type' => 'text', 'label' => 'Short Description', 'required' => 0, 'show' => 1);

    $this->insertOtherCustomField($fieldInfo, $tableInfo);
    //Fetching all Classified Custom Fields
    $fromCustomFields = $this->getFromDb()->select()
      ->from($this->getFromPrefix() . 'input_field', '*')
      ->where('module_id = ?', 'marketplace')
      ->query()
      ->fetchAll();
    //Creating the classified fields
    foreach( $fromCustomFields as $fromCustomField ) {
      $label = $this->getLabelByPharseVar($fromCustomField['phrase_var']);
      $is_required = ($fromCustomField['is_required'] == 1) ? 1 : 0;
      $fromCustomField['var_type'] = $this->getFieldType($fromCustomField['type_id']);
      $fromCustomField['label'] = $label;
      $fromCustomField['field_active'] = 1;
      $fromCustomField['is_required'] = $is_required;
      $fromCustomField['on_signup'] = 0;
      $fieldId = $this->insertClassifiedField($fromCustomField);
      $data = array
        (
        'fromFieldId' => $fromCustomField['field_id'],
        'toFieldId' => $fieldId
      );
      // Insert data into classifield field.
      $this->insertClassifiedCustomFieldData($data);
    }

    // Start Code regarding the inserting the Custom field for zipcode,city of Classified module
    $fieldInfo = array('type' => 'city', 'label' => 'City', 'required' => 0, 'show' => 1);
    $this->insertOtherCustomField($fieldInfo, $tableInfo);
    $fieldInfo = array('type' => 'zip_code', 'label' => 'Zip code', 'required' => 0, 'show' => 1);
    //Insert other custom fields
    $this->insertOtherCustomField($fieldInfo, $tableInfo);
    //Update some classified fields
    $this->_updateClassifiedField();
    // End Code regarding the inserting the Custom field for zipcode,city of Classified module  
  }

  protected function _insertCustomField()
  {

    // Fetching all Custom fields from source table
    $fromCustomFields = $this->getFromDb()->select()
        ->from($this->getfromPrefix() . 'custom_field', array
          (
          'field_id',
          'field_name',
          $this->getfromPrefix() . 'custom_field.phrase_var_name as field_phrase',
          $this->getfromPrefix() . 'custom_group.phrase_var_name as group_phrase',
          $this->getfromPrefix() . 'custom_field.type_name',
          $this->getfromPrefix() . 'custom_field.var_type',
          $this->getfromPrefix() . 'custom_field.is_active as field_active',
          $this->getfromPrefix() . 'custom_field.is_required',
          $this->getfromPrefix() . 'custom_field.has_feed',
          $this->getfromPrefix() . 'custom_field.on_signup',
          $this->getfromPrefix() . 'custom_field.ordering as field_order',
          $this->getfromPrefix() . 'custom_field.group_id as gid'
          )
        )
        ->join($this->getfromPrefix() . 'custom_group', $this->getfromPrefix() . 'custom_group.group_id=' . $this->getfromPrefix() . 'custom_field.group_id')
        ->where($this->getfromPrefix() . 'custom_field.module_id in (?)', array('user', 'custom'))
        ->order(array($this->getfromPrefix() . 'custom_group.group_id', $this->getfromPrefix() . 'custom_field.ordering'), 'ASC')
        ->query()->fetchAll();
    $grp = 0;
    // Traversing every custom fields
    foreach( $fromCustomFields as $fromCustomField ) {
      //find the pharse label using field phrase.
      $label = $this->getLabelByPharseVar($fromCustomField['field_phrase']);
      $fromCustomField['label'] = $label;
      //Insert the heading
      if( $grp != $fromCustomField['gid'] ) {
        $grp = $fromCustomField['gid'];
        $grpPhrase = $this->getLabelByPharseVar($fromCustomField['group_phrase']);
        $cFOptions = array(
          'field_id' => null,
          'var_type' => 'heading',
          'field_active' => 1,
          'is_required' => 0,
          'on_signup' => 0,
          'label' => $grpPhrase
        );
        //Insert heading
        $this->insertField($cFOptions);
      }
      // Insert Custom field
      $toFieldId = $this->insertField($fromCustomField);

      $fieldDataArr = array
        (
        'fieldName' => 'cf_' . $fromCustomField['field_name'],
        'toFieldId' => $toFieldId,
        'fromFieldId' => $fromCustomField['field_id'],
        'fieldType' => $this->getFieldType($fromCustomField['var_type'])
      );
      //Check wheater created custom field exist in phpfox or not
      $this->_isCustomFieldExist($fieldDataArr);
    }


    // Start Code regarding the inserting the Custom field for Country,zipcode,city of User module
    $tableInfo = array('fieldTable' => 'engine4_user_fields_meta', 'mapTable' => 'engine4_user_fields_maps', 'fieldId' => 1, 'optionId' => 1);
    $fieldInfo = array('type' => 'country', 'label' => 'Location', 'required' => 1, 'show' => 0);
    $this->insertOtherCustomField($fieldInfo, $tableInfo);

    $fieldInfo = array('type' => 'zip_code', 'label' => 'Zip code', 'required' => 0, 'show' => 0);
    $this->insertOtherCustomField($fieldInfo, $tableInfo);

    $fieldInfo = array('type' => 'city', 'label' => 'City', 'required' => 0, 'show' => 0);
    $this->insertOtherCustomField($fieldInfo, $tableInfo);
    // End Code regarding the inserting the Custom field for Country,zipcode,city of user module
  }

  /**
   * This function for updating the classified fields
   */
  public function _updateClassifiedField()
  {

    $metaTable = 'engine4_classified_fields_meta';
    //Find the field id of field type location
    $fieldId = $this->getToDb()
      ->select()
      ->from($metaTable, 'field_id')
      ->where('type = ?', 'location')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( !empty($fieldId) ) {
      // Change the location type to country type of custom field and change the label and alias
      $this->getToDb()
        ->query("update $metaTable set type='country', label='Location', alias='location' where field_id=$fieldId");
    }
    // Find row of currency type
    $fieldData = $this->getToDb()
      ->select()
      ->from($metaTable, array('field_id', 'config'))
      ->where('type = ?', 'currency')
      ->limit(1)
      ->query()
      ->fetch();
    if( !empty($fieldData) ) {
      $config = trim($fieldData['config']);
      $fieldId = $fieldData['field_id'];
      if( $config != '' ) {
        //decode the config from json to array
        $configArr = Zend_Json_Decoder::decode($config);
        $unit = "USD";
        // Previous stored currency unit is different from currently selected unit then update the config of currency field.
        if( isset($configArr['unit']) && $configArr['unit'] != $unit ) {
          $configArr['unit'] = $unit;
          //convert array to json 
          $configJson = Zend_Json_Encoder::encode($configArr);
          //update config in to currency field
          $this->getToDb()
            ->query("update $metaTable set config='$configJson' where field_id=$fieldId");
        }
      }
    }
  }

  /*
   * This function returns the field type.
   * Phpfox having differenct field type name from social engine.
   * This function returns the appropriate field type.
   */
  public function getFieldType($type)
  {
    $ttype = "";
    //find the field type and assign appropriate field type name.
    switch( $type ) {
      case 'shorttext':
        $ttype = 'text';
        break;
      case 'longtext':
        $ttype = 'textarea';
        break;
      case 'checkbox':
        $ttype = 'multi_checkbox';
        break;
      default :$ttype = $type;
    }
    return $ttype;
  }

  protected function insertClassifiedField($fromCustomField)
  {
    $metaTable = 'engine4_classified_fields_meta';
    $mapsTable = 'engine4_classified_fields_maps';
    $fieldValuesTable = 'engine4_classified_fields_values';
    $fieldOptionsTable = 'engine4_classified_fields_options';
    $fieldId = 0;
    $optionId = 0;

    $label = $fromCustomField['label'];
    //Checking weather custom field exist or not in Destination table
    $toFieldId = $fieldExist = $this->getToDb()->select()
      ->from($metaTable, 'field_id')
      ->where('label = ?', $label)
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    // if not exists then make entry into destination table.
    if( $fieldExist === false ) {

      $this->_profileFieldCount++;
      //Script for inserting custom field
      $this->getToDb()->insert($metaTable, array(
        'type' => (string) $this->getFieldType($fromCustomField['var_type']),
        'label' => (string) $label,
        'required' => (integer) $fromCustomField['is_required'],
        'show' => (integer) $fromCustomField['on_signup'],
        'order' => (integer) 999,
        'display' => (integer) $fromCustomField['field_active']
      ));


      $toFieldId = $this->getToDb()->lastInsertId();

      // Find all options of custom field from source table.
      if( !is_null($fromCustomField['field_id']) && !empty($fromCustomField['field_id']) ) {
        $fromFieldOptions = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'input_option', '*')
          ->where('field_id = ?', $fromCustomField['field_id'])
          ->query()
          ->fetchAll();

        // Traversing each field options
        if( $fromFieldOptions !== false && !is_null($fromFieldOptions) && count($fromFieldOptions) > 0 ) {

          foreach( $fromFieldOptions as $k => $fromFieldOption ) {

            $option = $this->getLabelByPharseVar($fromFieldOption['phrase_var']);
            // Inserting custion fields options into destination table
            $this->getToDb()->insert($fieldOptionsTable, array(
              'field_id' => (integer) $toFieldId,
              'label' => (string) $option,
              'order' => (integer) $fromFieldOption['ordering'],
            ));
            $this->_profileOptionCount++;
          }
        }
      }
      // Find maximum order in engine4_user_fields_maps table
      $order = $this->getToDb()->select()
        ->from($mapsTable, "max($mapsTable.order)")
        ->limit(1)
        ->query()
        ->fetchColumn(0);

      if( empty($order) || is_null($order) )
        $order = 1;
      else
        $order++;

      // Inserting data into engine4_classified_fields_maps table
      $this->getToDb()->insert($mapsTable, array(
        'field_id' => $fieldId,
        'option_id' => $optionId,
        'child_id' => $toFieldId,
        'order' => $order
        )
      );
      $this->_profileFieldsMap++;
    }
    return $toFieldId;
  }

  protected function insertField($fromCustomField)
  {
    $label = $fromCustomField['label'];
    //Checking weather custom field exist or not in Destination table
    $toFieldId = $fieldExist = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('label = ?', $label)
      ->where('type <> ?', 'heading')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    // if not exists then make entry into destination table.
    if( $fieldExist === false ) {

      $this->_profileFieldCount++;
      //Script for inserting custom field
      $this->getToDb()->insert('engine4_user_fields_meta', array(
        'type' => (string) $this->getFieldType($fromCustomField['var_type']),
        'label' => (string) $label,
        'required' => (integer) $fromCustomField['is_required'],
        'show' => (integer) $fromCustomField['on_signup'],
        'order' => (integer) 999,
        'display' => (integer) $fromCustomField['field_active']
      ));


      $toFieldId = $this->getToDb()->lastInsertId();

      // Find all options of custom field from source table.
      if( !is_null($fromCustomField['field_id']) && !empty($fromCustomField['field_id']) ) {
        $fromFieldOptions = $this->getFromDb()->select()
          ->from($this->getfromPrefix() . 'custom_option', '*')
          ->where('field_id = ?', $fromCustomField['field_id'])
          ->query()
          ->fetchAll();

        // Traversing each field options
        if( $fromFieldOptions !== false && !is_null($fromFieldOptions) && count($fromFieldOptions) > 0 ) {

          foreach( $fromFieldOptions as $k => $fromFieldOption ) {

            $option = $this->getLabelByPharseVar($fromFieldOption['phrase_var_name']);
            // Inserting custion fields options into destination table
            $this->getToDb()->insert('engine4_user_fields_options', array(
              'field_id' => (integer) $toFieldId,
              'label' => (string) $option,
              'order' => (integer) ($k + 1),
            ));
            $this->_profileOptionCount++;
          }
        }
      }
      // Find maximum order in engine4_user_fields_maps table
      $order = $this->getToDb()->select()
        ->from('engine4_user_fields_maps', 'max(engine4_user_fields_maps.order)')
        ->limit(1)
        ->query()
        ->fetchColumn(0);

      if( empty($order) || is_null($order) )
        $order = 1;
      else
        $order++;

      // Inserting data into engine4_user_fields_maps table
      $this->getToDb()->insert('engine4_user_fields_maps', array(
        'field_id' => 1,
        'option_id' => 1,
        'child_id' => $toFieldId,
        'order' => $order
        )
      );
      $this->_profileFieldsMap++;
    }
    return $toFieldId;
  }

  /*
   * This function use for creating the classified other custom fields 
   */
  public function insertOtherCustomField($fieldInfo, $tableInfo)
  {

    if( !isset($tableInfo['fieldTable']) || !isset($tableInfo['mapTable']) )
      return;
    //Checking existance of custom field
    $fieldExist = $this->getToDb()->select()
      ->from($tableInfo['fieldTable'], 'field_id')
      ->where('type = ?', $fieldInfo['type'])
      ->where('trim(lower(label)) = ?', trim(strtolower($fieldInfo['label'])))
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    // If already not exist then create new one.
    if( $fieldExist === false ) {
      $this->_profileFieldCount++;
      try {
        $this->getToDb()->insert($tableInfo['fieldTable'], array(
          'type' => (string) $fieldInfo['type'],
          'label' => (string) $fieldInfo['label'],
          'alias' => (string) $fieldInfo['label'],
          'required' => (integer) $fieldInfo['required'],
          'show' => (integer) $fieldInfo['show'],
          'order' => (integer) 999,
          'display' => 1
        ));
        $toFieldId = $this->getToDb()->lastInsertId();
        //fetching maximum order of field in map table.
        $order = $this->getToDb()->select()
          ->from($tableInfo['mapTable'], 'max(' . $tableInfo['mapTable'] . '.order)')
          ->limit(1)
          ->query()
          ->fetchColumn(0);
        if( empty($order) || is_null($order) )
          $order = 1;
        else
          $order++;

        // Inserting data into engine4_user_fields_maps table
        $this->getToDb()->insert($tableInfo['mapTable'], array(
          'field_id' => $tableInfo['fieldId'],
          'option_id' => $tableInfo['optionId'],
          'child_id' => $toFieldId,
          'order' => $order
          )
        );
        $this->_profileFieldsMap++;
      } catch( Exception $e ) {
        $this->_error('Problem in inserting custom fields : ' . $e->getMessage());
      }
    }
  }

  /*
   * This function returns the pharse label by taking the pharse variable.
   */
  public function getLabelByPharseVar($pharseVar)
  {
    if( empty($pharseVar) || is_null($pharseVar) )
      return '';
    //pharse var along with their module id so spliting module id and pharse var
    $pharseArr = explode('.', $pharseVar);
    if( count($pharseArr) != 2 )
      return '';
    // Selecting the pharse label
    $label = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'language_phrase', 'text')
      ->where('module_id = ?', $pharseArr[0])
      ->where('var_name = ?', $pharseArr[1])
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    if( is_null($label) || empty($label) )
      return '';

    return $label;
  }

  /*
   * This function returns true if a field contain multiple values otherwise false.
   */
  public function isFieldContainMultipleValue($type)
  {
    return $type == 'multi_checkbox' || $type == 'multiselect';
  }

  /*
   * Storing the Custom from field  id and To field id INTO SESSION
   */
  public function _isCustomFieldExist($data)
  {
    // search columns weather exist or not.
    $searchCols = $this->getFromDb()
      ->query('SHOW COLUMNS FROM ' . $this->getFromDb()->quoteIdentifier($this->getfromPrefix() . 'user_custom') . " where field='" . $data['fieldName'] . "'")
      ->fetch();
    // if field are not exist the skip those fields
    if( !is_null($searchCols) && count($searchCols) > 0 && isset($searchCols['Field']) ) {
      $this->setCustomFieldMap($data['fromFieldId'], $data);
    }
  }

  public function insertClassifiedCustomFieldData($data)
  {
    $metaTable = 'engine4_classified_fields_meta';
    $mapsTable = 'engine4_classified_fields_maps';
    $fieldValuesTable = 'engine4_classified_fields_values';
    $fieldOptionsTable = 'engine4_classified_fields_options';

    // Insert textarea field  data
    $fromCustomFieldData = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'input_value_longtext', '*')
      ->where('field_id = ?', $data['fromFieldId'])
      ->query()
      ->fetchAll();
    // Loop for insertion of each long text data of selected field
    foreach( $fromCustomFieldData as $fieldData ) {
      //Check data exist or not.
      $fromCustomFieldDataExists = $this->getToDb()->select()
        ->from($fieldValuesTable, 'field_id')
        ->where('item_id = ?', $fieldData['item_id'])
        ->where('field_id = ?', $data['toFieldId'])
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $fromCustomFieldDataExists === false ) {
        //Insert the data
        $this->getToDb()->insert($fieldValuesTable, array(
          'item_id' => $fieldData['item_id'],
          'field_id' => $data['toFieldId'],
          'value' => $fieldData['long_value']
        ));
      }
    }
    // Insert Short text value
    $fromCustomFieldData = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'input_value_shorttext', '*')
      ->where('field_id = ?', $data['fromFieldId'])
      ->query()
      ->fetchAll();
    // Loop for insertion of each Short text data of selected field
    foreach( $fromCustomFieldData as $fieldData ) {
      $fromCustomFieldDataExists = $this->getToDb()->select()
        ->from($fieldValuesTable, 'field_id')
        ->where('item_id = ?', $fieldData['item_id'])
        ->where('field_id = ?', $data['toFieldId'])
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $fromCustomFieldDataExists === false ) {
        $this->getToDb()->insert($fieldValuesTable, array(
          'item_id' => $fieldData['item_id'],
          'field_id' => $data['toFieldId'],
          'value' => $fieldData['full_value']
        ));
      }
    }
    // Insert Optional value
    $fromCustomFieldData = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'input_value_option', '*')
      ->where('field_id = ?', $data['fromFieldId'])
      ->query()
      ->fetchAll();

    foreach( $fromCustomFieldData as $fieldData ) {
      // fetching the phrase var
      $phraseVar = $this->getFromDb()
        ->select()
        ->from($this->getfromPrefix() . 'input_option', 'phrase_var')
        ->where('option_id = ?', $fieldData['option_id'])
        ->query()
        ->fetchColumn(0);
      // Fetching the label of phrase var
      $label = $this->getLabelByPharseVar($phraseVar);
      //Fetch field options
      $optionId = $this->getToDb()
        ->select()
        ->from($fieldOptionsTable, 'option_id')
        ->join($metaTable, "$metaTable.field_id=$fieldOptionsTable.field_id")
        ->where("$metaTable.field_id = ?", $data['toFieldId'])
        ->where('type not in (?)', "'text','textarea'")
        ->where("trim(lower($fieldOptionsTable.label)) = ?", trim(strtolower($label)))
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      if( $optionId === false || is_null($optionId) )
        $optionId = '';
      //fetching index id of a field
      $maxIndex = $this->getToDb()
        ->select()
        ->from($fieldValuesTable, "max($fieldValuesTable.index)")
        ->where('item_id = ?', $fieldData['item_id'])
        ->where('field_id = ?', $data['toFieldId'])
        ->limit(1)
        ->query()
        ->fetchColumn(0);
      // increment the index
      if( $maxIndex === false || is_null($maxIndex) )
        $maxIndex = 0;
      else
        $maxIndex++;
      //Insert the classfied custom field data
      $this->getToDb()
        ->insert
          (
          $fieldValuesTable, array
          (
          'item_id' => $fieldData['item_id'],
          'field_id' => $data['toFieldId'],
          'value' => $optionId,
          'index' => $maxIndex
          )
      );
    }
  }

  /**
   * 
   * This function should be called after creation of custom and other custom fields
   */
//    public function insertOtherCustomFieldData() {
//        //Select all users
//        $usersModel = $this->getFromDb()
//                ->select()
//                ->from($this->getfromPrefix() . 'user', '*')
//                ->query()
//                ->fetchAll();
//        foreach ($usersModel as $data) {
//            $birthdate = '';
//            //Select the remaining details of user
//            $userInfo = $this->getFromDb()
//                    ->select()
//                    ->from($this->getfromPrefix() . 'user_field', array('first_name', 'last_name', 'city_location as city', 'postal_code as zip_code'))
//                    ->where('user_id = ?', $data['user_id'])
//                    ->query()
//                    ->fetch();
//            //Fetching birthday detail
//            if (!is_null($data['birthday'])) {
//                $month = substr((string) $data['birthday'], 0, 2);
//                $day = substr((string) $data['birthday'], 2, 2);
//                $year = substr((string) $data['birthday'], 4, 4);
//                $birthdate = $year . '-' . $month . '-' . $day;
//            }
//            //Gender detail
//            $userInfo['gender'] = $data['gender'];
//            if (!is_null($data['gender']) && !empty($data['gender'])) {
//                if ($data['gender'] == 1) {
//                    $gender = 'Male';
//                } else if ($data['gender'] == 2) {
//                    $gender = 'Female';
//                }
//                if ($gender != '') {
//                    $toFieldId = $this->getToDb()->select()
//                            ->from('engine4_user_fields_meta', 'field_id')
//                            ->where('type = ?', 'gender')
//                            ->limit(1)
//                            ->query()
//                            ->fetchColumn(0);
//                    $userInfo['gender'] = $this->getToDb()
//                            ->select()
//                            ->from('engine4_user_fields_options', 'option_id')
//                            ->where('field_id = ?', $toFieldId)
//                            ->where('label = ?', $gender)
//                            ->query()
//                            ->fetchColumn(0);
//                }
//            }
//            //Set profile type "Regular Member" for each custom field
//            $userInfo['profile_type'] = 1;
//            //Fetching field id of having field type as "profile_type"
//            $toFieldId = $this->getToDb()->select()
//                    ->from('engine4_user_fields_meta', 'field_id')
//                    ->where('type = ?', 'profile_type')
//                    ->limit(1)
//                    ->query()
//                    ->fetchColumn(0);
//            //Select the option id of profile type field having option label "Regular Member"
//            if ($toFieldId) {
//                $userInfo['profile_type'] = $this->getToDb()
//                        ->select()
//                        ->from('engine4_user_fields_options', 'option_id')
//                        ->where('field_id = ?', $toFieldId)
//                        ->where('label = ?', 'Regular Member')
//                        ->query()
//                        ->fetchColumn(0);
//            }
//            //Set birthday 
//            $userInfo['birthdate'] = $birthdate;
//            $udata = array
//                (
//                'item_id' => $data['user_id'],
//                'profile_type' => (is_null($toFieldId) || empty($toFieldId)) ? 1 : $toFieldId,
//                'first_name' => $userInfo['first_name'],
//                'last_name' => $userInfo['last_name'],
//                'gender' => $userInfo['gender'],
//                'birthdate' => $userInfo['birthdate']
//            );
//            $userInfo['country'] = $data['country_iso'];
//            //Fetch location privacy
//            $location_user_value = $this->getFromDb()->select()
//                    ->from($this->getfromPrefix() . 'user_privacy', 'user_value')
//                    ->where('user_privacy = ?', 'profile.view_location')
//                    ->where('user_id = ?', $data['user_id'])
//                    ->query()
//                    ->fetchColumn();
//            $privacy = 'everyone';
//            if ($location_user_value == 1) {
//                $privacy = 'registered';
//            } else if ($location_user_value == 2) {
//                $privacy = 'friends';
//            } else if ($location_user_value == 4) {
//                $privacy = 'self';
//            }
//            //Insert custom field data
//            foreach ($userInfo as $key => $value) {
//                if ($key == 'country')
//                    $pvrcy = $privacy;
//                else
//                    $pvrcy = 'everyone';
//                $toFieldId = $this->getToDb()->select()
//                        ->from('engine4_user_fields_meta', 'field_id')
//                        ->where('type = ?', $key)
//                        ->limit(1)
//                        ->query()
//                        ->fetchColumn(0);
//                if ($toFieldId) {
//                    $value = is_null($value) ? '' : $value;
//                    $fromCustomFieldDataExists = $this->getToDb()->select()
//                            ->from('engine4_user_fields_values', 'field_id')
//                            ->where('item_id = ?', $data['user_id'])
//                            ->where('field_id = ?', $toFieldId)
//                            ->limit(1)
//                            ->query()
//                            ->fetchColumn(0);
//                    if ($fromCustomFieldDataExists === false) {
//                        $this->getToDb()
//                                ->insert('engine4_user_fields_values', array
//                                    (
//                                    'item_id' => $data['user_id'],
//                                    'field_id' => $toFieldId,
//                                    'index' => 0,
//                                    'value' => $value,
//                                    'privacy' => $pvrcy
//                                        )
//                        );
//                    }
//                }
//            }
//            //Insert User information into user field search
//            $this->_insertFieldSearch($udata);
//        }
//    }
//    protected function _insertFieldSearch($data) {
//        $this->getToDb()
//                ->insert('engine4_user_fields_search', array
//                    (
//                    'item_id' => $data['item_id'],
//                    'profile_type' => $data['profile_type'],
//                    'first_name' => $data['first_name'],
//                    'last_name' => $data['last_name'],
//                    'gender' => $data['gender'],
//                    'birthdate' => $data['birthdate']
//                        )
//        );
//        $this->_profileSearchCount++;
//    }

  protected function _translateRow(array $data, $key = null)
  {
    return true;
  }

}
