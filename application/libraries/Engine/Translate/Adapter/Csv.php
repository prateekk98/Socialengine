<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Csv.php 2017-02-6 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_Translate
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Translate_Adapter_Csv extends Engine_Translate_Adapter
{

  private $_data = array();

  /**
   * Generates the adapter
   *
   * @param  array|Zend_Config $options Translation content
   */
  public function __construct($options = array())
  {
    $this->_options['delimiter'] = ";";
    $this->_options['length'] = 0;
    $this->_options['enclosure'] = '"';

    if( $options instanceof Zend_Config ) {
      $options = $options->toArray();
    } elseif( func_num_args() > 1 ) {
      $args = func_get_args();
      $options = array();
      $options['content'] = array_shift($args);

      if( !empty($args) ) {
        $options['locale'] = array_shift($args);
      }

      if( !empty($args) ) {
        $opt = array_shift($args);
        $options = array_merge($opt, $options);
      }
    } elseif( !is_array($options) ) {
      $options = array('content' => $options);
    }

    parent::__construct($options);
  }

  /**
   * Load translation data
   *
   * @param  string|array  $filename  Filename and full path to the translation source
   * @param  string        $locale    Locale/Language to add data for, identical with locale identifier,
   *                                  see Zend_Locale for more information
   * @param  array         $option    OPTIONAL Options to use
   * @return array
   */
  protected function _loadTranslationData($filename, $locale, array $options = array())
  {
    $this->_data = array();
    $options = $options + $this->_options;
    $this->_file = @fopen($filename, 'rb');
    if( !$this->_file ) {
      throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
    }

    while( ($data = fgetcsv($this->_file, $options['length'], $options['delimiter'], $options['enclosure'])) !== false ) {
      if( substr($data[0], 0, 1) === '#' ) {
        continue;
      }

      if( !isset($data[1]) ) {
        continue;
      }

      if( count($data) == 2 ) {
        $this->_data[$locale][$data[0]] = $data[1];
      } else {
        $singular = array_shift($data);
        $this->_data[$locale][$singular] = $data;
      }
    }

    return $this->_data;
  }

  /**
   * overwrite
   */
  public function toString()
  {
    return "Csv";
  }

}
