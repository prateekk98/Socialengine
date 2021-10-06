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
class Install_Import_Phpfox_UserOtherFieldsData extends Install_Import_Phpfox_Abstract
{

  protected $_fromTable = '';
  protected $_toTable = '';
  protected $_priority = 4998;
  protected $_customFields = array();

  protected function _initPre()
  {
    $this->_fromTable = $this->getFromPrefix() . 'user';
    $this->_toTable = 'engine4_user_fields_values';
    $this->_truncateTable($this->getToDb(), 'engine4_user_fields_search');
  }

  protected function _runPre()
  {
    $this->_customFields = array();
    $fieldArr = array();
    //Fetching gender field id
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'gender')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['gender'] = $toFieldId;
    //Fetching male option id
    $userMaleOptionId = $this->getToDb()
      ->select()
      ->from('engine4_user_fields_options', 'option_id')
      ->where('field_id = ?', $toFieldId)
      ->where('label = ?', 'Male')
      ->query()
      ->fetchColumn(0);
    //Fetching female option id
    $userFemaleOptionId = $this->getToDb()
      ->select()
      ->from('engine4_user_fields_options', 'option_id')
      ->where('field_id = ?', $toFieldId)
      ->where('label = ?', 'Male')
      ->query()
      ->fetchColumn(0);
    //Fetching option id and profile type into _customFields array
    $this->_customFields = array(
      'Male' => $userMaleOptionId,
      'Female' => $userFemaleOptionId,
      'profile_type' => 0
    );
    //Fetching field id of having field type as "profile_type"
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'profile_type')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['profile_type'] = $toFieldId;
    //Select the option id of profile type field having option label "Regular Member"
    if( $toFieldId ) {
      $this->_customFields['profile_type'] = $this->getToDb()
        ->select()
        ->from('engine4_user_fields_options', 'option_id')
        ->where('field_id = ?', $toFieldId)
        ->where('label = ?', 'Regular Member')
        ->query()
        ->fetchColumn(0);
    }
    //Fetching birthday field id and store it into array
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'birthdate')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['birthdate'] = $toFieldId;
    //Fetching country field id and store it into array
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'country')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['country'] = $toFieldId;
    //Fetching last_name field id and store it into array
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'last_name')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['last_name'] = $toFieldId;
    //Fetching first_name field id and store it into array
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'first_name')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['first_name'] = $toFieldId;
    //Fetching city field id and store it into array
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'city')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['city'] = $toFieldId;
    //Fetching zip_code field id and store it into array
    $toFieldId = $this->getToDb()->select()
      ->from('engine4_user_fields_meta', 'field_id')
      ->where('type = ?', 'zip_code')
      ->limit(1)
      ->query()
      ->fetchColumn(0);
    $fieldArr['zip_code'] = $toFieldId;
    $this->_customFields['fieldsId'] = $fieldArr;
  }

  protected function _translateRow(array $data, $key = null)
  {

    $birthdate = '';
    //Select the remaining details of user
    $userInfo = $this->getFromDb()
      ->select()
      ->from($this->getfromPrefix() . 'user_field', array('first_name', 'last_name', 'city_location as city', 'postal_code as zip_code'))
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetch();
    //Fetching birthday detail
    if( !is_null($data['birthday']) ) {
      $month = substr((string) $data['birthday'], 0, 2);
      $day = substr((string) $data['birthday'], 2, 2);
      $year = substr((string) $data['birthday'], 4, 4);
      $birthdate = $year . '-' . $month . '-' . $day;
    }
    //Gender detail
    $userInfo['gender'] = $data['gender'];
    if( !is_null($data['gender']) && !empty($data['gender']) ) {
      if( $data['gender'] == 1 ) {
        $gender = 'Male';
      } else if( $data['gender'] == 2 ) {
        $gender = 'Female';
      }
      if( $gender != '' ) {
        $userInfo['gender'] = $this->_customFields[$gender];
      }
    }
    //Set profile type "Regular Member" for each custom field
    $userInfo['profile_type'] = 1;
    if( $this->_customFields['profile_type'] != 0 )
      $userInfo['profile_type'] = $this->_customFields['profile_type'];

    //Set birthday 
    $userInfo['birthdate'] = $birthdate;
    $toFieldId = $this->_customFields['fieldsId']['profile_type'];
    $udata = array
      (
      'item_id' => $data['user_id'],
      'profile_type' => (is_null($toFieldId) || empty($toFieldId)) ? 1 : $toFieldId,
      'first_name' => $userInfo['first_name'],
      'last_name' => $userInfo['last_name'],
      'gender' => $userInfo['gender'],
      'birthdate' => $userInfo['birthdate']
    );
    $userInfo['country'] = $data['country_iso'];
    //Fetch location privacy
    $location_user_value = $this->getFromDb()->select()
      ->from($this->getfromPrefix() . 'user_privacy', 'user_value')
      ->where('user_privacy = ?', 'profile.view_location')
      ->where('user_id = ?', $data['user_id'])
      ->query()
      ->fetchColumn();
    $privacy = 'everyone';
    if( $location_user_value == 1 ) {
      $privacy = 'registered';
    } else if( $location_user_value == 2 ) {
      $privacy = 'friends';
    } else if( $location_user_value == 4 ) {
      $privacy = 'self';
    }
    //Insert custom field data
    foreach( $userInfo as $key => $value ) {
      if( $key == 'country' )
        $pvrcy = $privacy;
      else
        $pvrcy = 'everyone';
      $toFieldId = $this->_customFields['fieldsId'][$key];
      if( $toFieldId ) {
        $value = is_null($value) ? '' : $value;

        $this->getToDb()
          ->insert('engine4_user_fields_values', array
            (
            'item_id' => $data['user_id'],
            'field_id' => $toFieldId,
            'index' => 0,
            'value' => $value,
            'privacy' => $pvrcy
            )
        );
      }
    }
    //Insert User information into user field search
    $this->_insertFieldSearch($udata);
  }
}
