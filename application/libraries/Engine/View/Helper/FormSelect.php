<?php
/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: FormRadio.php 9747 2012-07-26 02:08:08Z john $
 * @todo       documentation
 */

/**
 * @category   Engine
 * @package    Engine_View
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_View_Helper_FormSelect extends Zend_View_Helper_FormSelect
{
  /**
   * @overwrite
   */
  public function formSelect($name, $value = null, $attribs = null, $options = null, $listsep = "<br />\n")
  {
    $info = $this->_getInfo($name, $value, $attribs, $options, $listsep);
    extract($info); // name, id, value, attribs, options, listsep, disable
    // force $value to array so we can compare multiple values to multiple
    // options; also ensure it's a string for comparison purposes.
    $value = array_map('strval', (array) $value);

    // check if element may have multiple values
    $multiple = '';

    if( substr($name, -2) == '[]' ) {
      // multiple implied by the name
      $multiple = ' multiple="multiple"';
    }

    if( isset($attribs['multiple']) ) {
      // Attribute set
      if( $attribs['multiple'] ) {
        // True attribute; set multiple attribute
        $multiple = ' multiple="multiple"';

        // Make sure name indicates multiple values are allowed
        if( !empty($multiple) && (substr($name, -2) != '[]') ) {
          $name .= '[]';
        }
      } else {
        // False attribute; ensure attribute not set
        $multiple = '';
      }
      unset($attribs['multiple']);
    }

    // handle the options classes
    $optionClasses = array();
    if( isset($attribs['optionClasses']) ) {
      $optionClasses = $attribs['optionClasses'];
      unset($attribs['optionClasses']);
    }

    // now start building the XHTML.
    $disabled = '';
    if( true === $disable ) {
      $disabled = ' disabled="disabled"';
    }

    // Build the surrounding select element first.
    $xhtml = '<select'
      . ' name="' . $this->view->escape($name) . '"'
      . ' id="' . $this->view->escape($id) . '"'
      . $multiple
      . $disabled
      . $this->_htmlAttribs($attribs)
      . ">\n    ";

    // build the list of options
    $list = array();
    $translator = $this->getTranslator();
    if( $multiple ) {
      // Added for fix ios multiselect issue
      $list[] = '<optgroup style="height:0px;" class="options-default-disabled" disabled="disabled"></optgroup>';
    }
    foreach( (array) $options as $optValue => $optLabel ) {
      if( is_array($optLabel) ) {
        $optDisable = '';
        if( is_array($disable) && in_array($optValue, $disable) ) {
          $optDisable = ' disabled="disabled"';
        }
        if( null !== $translator ) {
          $optValue = $translator->translate($optValue);
        }
        $optId = ' id="' . $this->view->escape($id) . '-optgroup-'
          . $this->view->escape($optValue) . '"';
        $list[] = '<optgroup'
          . $optDisable
          . $optId
          . ' label="' . $this->view->escape($optValue) . '">';
        foreach( $optLabel as $val => $lab ) {
          $list[] = $this->_build($val, $lab, $value, $disable, $optionClasses);
        }
        $list[] = '</optgroup>';
      } else {
        $list[] = $this->_build($optValue, $optLabel, $value, $disable, $optionClasses);
      }
    }

    // add the options to the xhtml and close the select
    $xhtml .= implode("\n    ", $list) . "\n</select>";

    return $xhtml;
  }
}
