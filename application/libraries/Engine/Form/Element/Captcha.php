<?php

/**
 * SocialEngine
 *
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 * @version    $Id: Captcha.php 9747 2012-07-26 02:08:08Z john $
 */

/**
 * @category   Engine
 * @package    Engine_Form
 * @copyright  Copyright 2006-2020 Webligo Developments
 * @license    http://www.socialengine.com/license/
 */
class Engine_Form_Element_Captcha extends Zend_Form_Element_Captcha
{

  /**
   * Load default decorators
   *
   * @return void
   */
  public function loadDefaultDecorators()
  {
    if( $this->loadDefaultDecoratorsIsDisabled() ) {
      return;
    }

    $decorators = $this->getDecorators();
    if( empty($decorators) ) {
      Engine_Form::addDefaultDecorators($this);
    }
  }

  /**
   * Retrieve plugin loader for validator or filter chain
   *
   * Support for plugin loader for Captcha adapters
   *
   * @param  string $type
   * @return Zend_Loader_PluginLoader
   * @throws Zend_Loader_Exception on invalid type.
   */
  public function getPluginLoader($type)
  {
    $type = strtoupper($type);
    if( $type == self::CAPTCHA ) {
      if( !isset($this->_loaders[$type]) ) {
        $this->_loaders[$type] = new Zend_Loader_PluginLoader(
          array('Zend_Captcha' => 'Zend/Captcha/', 'Engine_Captcha' => 'Engine/Captcha/')
        );
      }
      return $this->_loaders[$type];
    } else {
      return parent::getPluginLoader($type);
    }
  }
}
