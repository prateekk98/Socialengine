<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Array.php 2017-02-6 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Translate_Adapter_Array extends Engine_Translate_Adapter
{
  private $_data = array();

  /**
   * Load translation data
   *
   * @param  string|array  $data
   * @param  string        $locale  Locale/Language to add data for, identical with locale identifier,
   *                                see Zend_Locale for more information
   * @param  array         $options OPTIONAL Options to use
   * @return array
   */
  protected function _loadTranslationData($data, $locale, array $options = array())
  {
    $this->_data = array();
    if( !is_array($data) ) {
      if( file_exists($data) ) {
        ob_start();
        $data = include($data);
        ob_end_clean();
      }
    }
    if( !is_array($data) ) {
      throw new Zend_Translate_Exception("Error including array or file '" . $data . "'");
    }

    if( !isset($this->_data[$locale]) ) {
      $this->_data[$locale] = array();
    }

    $this->_data[$locale] = $data + $this->_data[$locale];
    return $this->_data;
  }

  /**
   * returns the adapters name
   *
   * @return string
   */
  public function toString()
  {
    return "Array";
  }

}
