<?php

/**
 * SocialEngine
 *
 * @category   Application_Extensions
 * @package    Phpfoximporter
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    ClassifiedFields.php 2015-07-30 00:00:00Z john $
 * @author     John
 */
class Install_Import_Phpfox_ClassifiedFields extends Install_Import_Phpfox_AbstractFields
{

  protected $_toTableTruncate = false;
  protected $_toResourceType = 'classified';
  protected $_isTableExist = array('engine4_classified_classifieds');
  protected $_priority = 4500;
  protected $_warningMessage = array("key" => 'general', 'value' => '<span style="font-weight:bolder;">Warning:</span>  Your SocialEngine PHP site have not installed some of the SocialEngine official core plugins, which leads to not migrating the related content of those plugins. You may ignore this message or you can install these plugins to migrate the related content of these plugins. <a target="_blank" href="manage" > Click here</a>  to install these plugins.');

  /*
   * Don't remove this function 
   */
  protected function _runPre()
  {
    //Overwriting this function
  }

  protected function _run()
  {

    //INSERT THE CLASSIFIED CUSTOM FIELD
    $this->_insertClassifiedCustomField();
    $this->_insertOtherClassifiedCustomData();
    $this->_message(sprintf('Success - %d profile fields records imported', $this->_profileFieldCount));
    $this->_message(sprintf('Success - %d profile options records imported', $this->_profileOptionCount));
    $this->_message(sprintf('Success - %d profile search records imported', $this->_profileSearchCount));
    $this->_message(sprintf('Success - %d profile field maps records imported', $this->_profileFieldsMap));
  }

}
